<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\FixedAssets;

use function Laravel\Prompts\table;

class FixedAssetsImport implements ToCollection
{
    protected $data = [];
    protected $firstRow = true; // Flag to exclude the first row

    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            if ($this->firstRow) {
                $this->firstRow = false;
                continue; // Skip the first row
            }

            // Check if email field is not empty
            if (!empty($row[0])) {
                $flag = 0;
                $class = $row[0];
                $description = $row[1];
                $location = $row[2];
                $reference = $row[3];
                $cost = str_replace(' ', '', $row[4]);
                $cost = str_replace(',', '', $cost);
                $date = intval($row[5]);
                //$capitalization = $this->transformDate($row[5]);
                $capitalization = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
                if(date('Y',strtotime($capitalization)) == 1900){
                    $capitalization = str_replace('/', '-', $row[5]);
                    $capitalization = date('Y-m-d', strtotime($capitalization));
                    //dd($capitalization);
                }
                
                //dd($capitalization);

                $data = DB::table('manage_fixed_assets')->where(['class' => $class, 'description' => $description])->get();
                foreach($data as $row){
                    $datas['class_id'] = $row -> class_id;
                    $datas['life'] = $row -> life;
                    $datas['depreciation'] = $row -> depreciation;
                    $datas['ncoa'] = $row -> ncoa;
                    $datas['disposal_date'] = date('Y-m-d', strtotime($capitalization.' '.$row -> life.' years'));
                    $flag = 1;
                }
                $datas['class'] = $class;
                $datas['description'] = $description;
                $datas['location'] = $location;
                $datas['reference'] = $reference;
                $datas['cost'] = $cost;
                $datas['capitalization'] = date('Y-m-d',strtotime($capitalization));
                $datas['month'] = date('m',strtotime($capitalization));
                $datas['year'] = date('Y',strtotime($capitalization));
                $datas['created_at'] = now();
                $datas['updated_at'] = now();
                $datas = array_map('strtoupper', $datas);
                if($flag == 1 && is_numeric($cost) == true){
                    DB::table('fixed_assets')->insert($datas);
                }
                
            }
        }
    }
}

