// app/Http/Controllers/AdminUserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index() {
        return User::select('id','username')->orderBy('id')->get();
    }

    public function store(Request $r) {
        $data = $r->validate([
            'username' => 'required|string|min:3|max:60|unique:users,username',
            'password' => 'required|string|min:4|max:100',
        ]);
        $u = User::create([
            'username' => $data['username'],
            'name'     => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
        return response()->json(['id'=>$u->id,'username'=>$u->username], 201);
    }

    public function destroy($id) {
        User::where('id',$id)->delete();
        return response()->noContent();
    }

    public function login(Request $r) {
        $data = $r->validate(['username'=>'required','password'=>'required']);
        $u = User::where('username',$data['username'])->first();
        if (!$u || !Hash::check($data['password'], $u->password)) {
            return response()->json(['message'=>'Credenciales inválidas'], 401);
        }
        return response()->json(['ok'=>true,'user'=>['id'=>$u->id,'username'=>$u->username]]);
    }
}
