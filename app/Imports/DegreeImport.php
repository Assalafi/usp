<?php
namespace App\Imports;

use App\Models\HostelPin;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DegreeImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true;  // Flag to exclude the first row

    private $degree;

    public function __construct($degree)
    {
        $this->degree = $degree;
    }

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue;  // Skip the first row
            }
            if (!empty($row[1])) {
                try {
                    // check sure username exist before updating the degree
                    if (Staff::where(['username' => $row[1]])->exists()) {
                        Staff::where(['username' => $row[1]])->update([
                            'degree' => $this->degree
                        ]);
                    }
                } catch (QueryException $e) {
                    return redirect()->back()->with('error', 'Something went Wrong.');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Something went Wrong...');
                } finally {
                }
            }
        }
    }
}
