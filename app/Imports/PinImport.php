<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\HostelPin;

class PinImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    private $batch;

    public function __construct($batch)
    {
        $this->batch = $batch;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }
            if (!empty($row[0])) {
                try {
                    $pin = HostelPin::select('id','username')->where('username', str_replace(' ', '', $row[1]))->first();
                    $id = HostelPin::select('id')->where(['username' => 'Awaiting', 'batch' => $this -> batch])->limit(1)->value('id');
                    if($pin){

                    }else{
                        HostelPin::where(['id' => $id,'username' => 'Awaiting', 'batch' => $this -> batch])->update([
                            'username' => str_replace(' ', '', $row[1])
                        ]);
                    }

                } catch (QueryException $e) {
                    return redirect()->back()->with('error', 'Something went Wrong.');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Something went Wrong...');
                } finally {}
            }
        }
    }
}

