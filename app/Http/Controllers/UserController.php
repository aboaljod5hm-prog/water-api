<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ── عرض المستخدمين (بدون المدير العام) ──
    public function index(Request $request)
    {
        $this->ensureAdminGeneral($request->user());
        return response()->json(User::where('role', '!=', 'admin_general')->get());
    }

    // ── إضافة مستخدم ──
    public function store(Request $request)
    {
        $this->ensureAdminGeneral($request->user());

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,worker',
        ]);

        $data['password'] = Hash::make($data['password']);
        return response()->json(User::create($data), 201);
    }

    // ── تعديل مستخدم ──
    public function update(Request $request, User $user)
    {
        $this->ensureAdminGeneral($request->user());

        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'role'     => 'sometimes|in:admin,worker',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user->fresh());
    }

    // ── حذف مستخدم ──
    public function destroy(Request $request, User $user)
    {
        $this->ensureAdminGeneral($request->user());

        if ($user->role === 'admin_general') {
            return response()->json(['message' => 'لا يمكن حذف المدير العام'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'تم حذف المستخدم']);
    }

    private function ensureAdminGeneral($user)
    {
        if ($user->role !== 'admin_general') {
            abort(403, 'هذه الصلاحية للمدير العام فقط');
        }
    }
}
