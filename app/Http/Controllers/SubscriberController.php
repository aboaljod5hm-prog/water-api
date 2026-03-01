<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SubscriberController extends Controller
{
    public function stats()
{
    return response()->json([
        'total'      => Subscriber::count(),
        'read'       => Subscriber::whereNotNull('reading_date')->count(),
        'unread'     => Subscriber::whereNull('reading_date')->count(),
        'sections'   => Subscriber::distinct('section_no')->count('section_no'),
        'recent'     => Subscriber::whereNotNull('reading_date')
                            ->orderBy('reading_date', 'desc')
                            ->take(5)
                            ->get()
    ]);
}
    // ── عرض جميع المشتركين (جميع الأدوار) ──
    public function index(Request $request)
{
    $query = Subscriber::query();

    if ($request->search) {
        $q = $request->search;

        $query->where(function ($q2) use ($q) {
            $q2->where('name', 'like', "%{$q}%")
               ->orWhere('meter_no', 'like', "%{$q}%")
               ->orWhere('section_no', 'like', "%{$q}%");
        });
    }

    return response()->json(
        $query->orderBy('id', 'desc')->paginate(10)
    );
}
    // ── إضافة مشترك (مدير فقط) ──
    public function store(Request $request)
    {
        $this->ensureRole($request->user(), ['admin', 'admin_general']);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'meter_no'   => 'required|string|unique:subscribers,meter_no',
            'section_no' => 'required|string|unique:subscribers,section_no',
            'reading'    => 'nullable|numeric|min:0',
        ],
    [
            'meter_no.unique'   => 'رقم العداد مستخدم مسبقاً',
            'section_no.unique' => 'رقم المقسم مستخدم مسبقاً',
        ]);

        if ($request->filled('reading')) {
    $data['reading_date'] = Carbon::today();
}

        return response()->json(Subscriber::create($data), 201);
    }

    // ── تعديل مشترك ──
    public function update(Request $request, Subscriber $subscriber)
{
    $user = $request->user();

    if ($user->role === 'worker') {

        // 👇 مهم جداً: nullable وليس required
        $data = $request->validate([
            'reading' => 'nullable|numeric|min:0'
        ]);

        if (array_key_exists('reading', $data)) {

            if ($data['reading'] === null) {
                $subscriber->update([
                    'reading'      => null,
                    'reading_date' => null,
                ]);
            } else {
                $subscriber->update([
                    'reading'      => $data['reading'],
                    'reading_date' => Carbon::today(),
                ]);
            }
        }

    } else {

        $data = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'meter_no'   => 'sometimes|string|unique:subscribers,meter_no,' . $subscriber->id,
            'section_no' => 'sometimes|string|unique:subscribers,section_no,' . $subscriber->id,
            'reading'    => 'nullable|numeric|min:0',
        ],[
            'meter_no.unique'   => 'رقم العداد مستخدم مسبقاً',
            'section_no.unique' => 'رقم المقسم مستخدم مسبقاً',
        ]);

        if (array_key_exists('reading', $data)) {

            if ($data['reading'] === null) {
                $data['reading_date'] = null;
            } else {
                $data['reading_date'] = Carbon::today();
            }
        }

        $subscriber->update($data);
    }

    return response()->json($subscriber->fresh());
}

    // ── حذف مشترك (مدير فقط) ──
    public function destroy(Request $request, Subscriber $subscriber)
    {
        $this->ensureRole($request->user(), ['admin', 'admin_general']);
        $subscriber->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    // ── تصدير Excel ──
    public function export()
    {
        $subscribers = Subscriber::orderBy('id')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        // رؤوس الأعمدة
        $headers = ['#', 'الاسم', 'رقم العداد', 'رقم المقسم', 'القراءة م³', 'تاريخ القراءة', 'آخر تعديل'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // البيانات
        foreach ($subscribers as $i => $s) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $s->name);
            $sheet->setCellValueByColumnAndRow(3, $row, $s->meter_no);
            $sheet->setCellValueByColumnAndRow(4, $row, $s->section_no);
            $sheet->setCellValueByColumnAndRow(5, $row, $s->reading);
            $sheet->setCellValueByColumnAndRow(6, $row, $s->reading_date ? $s->reading_date->format('Y-m-d') : '');
            $sheet->setCellValueByColumnAndRow(7, $row, $s->updated_at ? $s->updated_at->format('Y-m-d') : '');
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'مشتركو_دائرة_المياه_' . now()->format('Y-m-d') . '.xlsx';
        $path     = storage_path('app/' . $filename);
        $writer->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    // ── استيراد Excel ──
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getPathname());
        $rows        = $spreadsheet->getActiveSheet()->toArray();

        // تخطي الصف الأول (رؤوس الأعمدة)
        array_shift($rows);

        $added = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $name      = trim($row[0] ?? '');
$meterNo   = trim($row[1] ?? '');
$sectionNo = trim($row[2] ?? '');

$readingRaw = $row[3] ?? null;
$reading = ($readingRaw !== null && $readingRaw !== '')
    ? floatval($readingRaw)
    : null;

            if (!$name || !$meterNo || !$sectionNo) continue;

            // تخطي إذا العداد موجود مسبقاً
if (Subscriber::where('meter_no', $meterNo)->exists()) {
    $errors[] = "رقم العداد {$meterNo} موجود مسبقاً";
    continue;
}

// تخطي إذا رقم المقسم موجود مسبقاً
if (Subscriber::where('section_no', $sectionNo)->exists()) {
    $errors[] = "رقم المقسم {$sectionNo} موجود مسبقاً";
    continue;
}

            Subscriber::create([
                'name'         => $name,
                'meter_no'     => $meterNo,
                'section_no'   => $sectionNo,
                'reading'      => $reading,
                'reading_date' => $reading !== null
    ? Carbon::today()
    : null,
            ]);
            $added++;
        }

        return response()->json([
            'message' => "تم استيراد {$added} مشترك بنجاح",
            'added'   => $added,
            'errors'  => $errors,
        ]);
    }

    // ── Helper ──
    private function ensureRole($user, array $roles)
    {
        if (!in_array($user->role, $roles)) {
            abort(403, 'غير مصرح لك بهذا الإجراء');
        }
    }
    public function destroyAll(Request $request)
{
    $user = $request->user();

    if (!in_array($user->role, ['admin', 'admin_general'])) {
        return response()->json(['message' => 'غير مصرح لك'], 403);
    }

    \App\Models\Subscriber::truncate();

    return response()->json(['message' => 'تم حذف جميع المشتركين بنجاح']);
}
}
