<?php
/**
 * @package FilesController
 * @author TechVillage <support@techvill.org>
 * @contributor Sakawat Hossain Rony <[sakawat.techvill@gmail.com]>
 * @created 12-10-2021
 */
namespace Modules\Ticket\Http\Controllers\User;

use Illuminate\Routing\Controller;
use App\Models\File;

class FilesController extends Controller
{
    public function downloadAttachment($id)
    {
        return (new File)->downloadAttachment($id);
    }
}
