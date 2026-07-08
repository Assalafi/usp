<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\Step;

class ReferenceDataController extends Controller
{
    private function getModel($type) {
        return match($type) {
            'unit' => Unit::class,
            'designation' => Designation::class,
            'grade' => Grade::class,
            'step' => Step::class,
        };
    }

    private function getTableName($type) {
        return match($type) {
            'unit' => 'units',
            'designation' => 'designations',
            'grade' => 'grades',
            'step' => 'steps',
        };
    }

    public function index($type) {
        if (!session()->has('log')) return redirect('/');

        $model = $this->getModel($type);
        $data = $model::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        $viewData = [
            'type' => $type,
            'data' => $data,
            'page' => 'reference-data/index'
        ];
        return view('main', $viewData);
    }

    public function create($type) {
        if (!session()->has('log')) return redirect('/');
        $viewData = [
            'type' => $type,
            'page' => 'reference-data/create'
        ];
        return view('main', $viewData);
    }

    public function store(Request $req, $type) {
        if (!session()->has('log')) return redirect('/');

        $model = $this->getModel($type);
        $model::create([
            'name' => strtoupper($req->name),
            'order' => $req->order ?? 0,
            'status' => $req->status ?? '1',
        ]);

        return redirect("/reference-data/$type")->with('success', ucfirst($type) . ' created successfully!');
    }

    public function edit($type, $id) {
        if (!session()->has('log')) return redirect('/');

        $model = $this->getModel($type);
        $item = $model::find($id);
        $viewData = [
            'type' => $type,
            'item' => $item,
            'page' => 'reference-data/edit'
        ];
        return view('main', $viewData);
    }

    public function update(Request $req, $type, $id) {
        if (!session()->has('log')) return redirect('/');

        $model = $this->getModel($type);
        $model::find($id)->update([
            'name' => strtoupper($req->name),
            'order' => $req->order ?? 0,
            'status' => $req->status ?? '1',
        ]);

        return redirect("/reference-data/$type")->with('success', ucfirst($type) . ' updated successfully!');
    }

    public function delete($type, $id) {
        if (!session()->has('log')) return redirect('/');

        $model = $this->getModel($type);
        $model::destroy($id);

        return redirect("/reference-data/$type")->with('success', ucfirst($type) . ' deleted successfully!');
    }

    public function bulkUpload($type) {
        if (!session()->has('log')) return redirect('/');
        $viewData = [
            'type' => $type,
            'page' => 'reference-data/bulk-upload'
        ];
        return view('main', $viewData);
    }

    public function processBulkUpload(Request $req, $type) {
        if (!session()->has('log')) return redirect('/');

        $req->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $model = $this->getModel($type);
        $file = $req->file('file');

        if ($file->getClientOriginalExtension() === 'csv') {
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle);
            $imported = 0;
            while (($row = fgetcsv($handle)) !== FALSE) {
                $model::updateOrCreate(
                    ['name' => strtoupper($row[0])],
                    ['order' => $row[1] ?? 0, 'status' => $row[2] ?? '1']
                );
                $imported++;
            }
            fclose($handle);
        } else {
            // Excel processing would need Laravel Excel package
            return redirect()->back()->with('error', 'Excel upload requires Laravel Excel package. Please use CSV format.');
        }

        return redirect("/reference-data/$type")->with('success', "$imported records imported successfully!");
    }

    public function downloadTemplate($type) {
        $headers = ['Name', 'Order', 'Status'];
        $filename = $type . '_template.csv';

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }
}
