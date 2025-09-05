// app/Http/Controllers/LicenseController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    public function status(Request $r) {
        $device = (string) $r->query('device_id', '');
        $row = $device ? DB::table('licenses')->where('device_id',$device)->first() : null;
        if (!$row) {
            return response()->json([
                'activated'=>false,'code'=>null,'deviceId'=>$device ?: null,
                'expiresAt'=>null,'daysRemaining'=>null,'message'=>'Licencia no activada'
            ]);
        }
        $days = $row->expires_at ? now()->diffInDays($row->expires_at, false) : null;
        return response()->json([
            'activated'=>(bool)$row->activated_at,
            'code'=>$row->code,'deviceId'=>$row->device_id,
            'expiresAt'=>optional($row->expires_at)->toDateString(),
            'daysRemaining'=>$days,
            'message'=>$days!==null && $days>=0
                ? "Faltan {$days} días para finalizar la licencia."
                : "La licencia ha expirado."
        ]);
    }

    public function activate(Request $r) {
        $data = $r->validate([
            'code'      => 'required|string|max:64',
            'device_id' => 'required|string|max:128',
        ]);
        $expires = now()->addDays((int) env('LICENSE_DAYS', 365));
        DB::table('licenses')->updateOrInsert(
            ['code'=>$data['code'],'device_id'=>$data['device_id']],
            ['activated_at'=>now(),'expires_at'=>$expires,'updated_at'=>now(),'created_at'=>now()]
        );
        return $this->status(new Request(['device_id'=>$data['device_id']]));
    }
}
