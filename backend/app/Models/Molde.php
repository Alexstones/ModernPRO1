// app/Models/Molde.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Molde extends Model
{
    protected $fillable = [
        'nombre','camiseta_izq','camiseta_der','short_izq','short_der','manga_izq','manga_der'
    ];
}
