<?php

namespace App\Http\Controllers;

use App\Imports\SalesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        return view('dashboard.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);

        try {
            $import = SalesImport::previewFromFile($fullPath);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $previewData = array_slice($import->getCleanedData(), 0, 20);
        $cleaningLog = $import->getCleaningLog();

        return view('dashboard.import', [
            'previewData' => $previewData,
            'cleaningLog' => $cleaningLog,
            'originalCount' => $import->getOriginalCount(),
            'cleanedCount' => $import->getCleanedCount(),
            'tempFile' => $path,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'temp_file' => 'required|string',
        ]);

        $fullPath = Storage::path($request->input('temp_file'));

        if (!file_exists($fullPath)) {
            return back()->with('error', 'File temporary tidak ditemukan. Silakan upload ulang.');
        }

        try {
            \App\Models\Sales::truncate();
            $import = SalesImport::previewFromFile($fullPath);
            $inserted = $import->importAndSave();
            Storage::delete($request->input('temp_file'));

            return redirect()->route('dashboard')->with('success', "Data berhasil diimport! {$inserted} records disimpan setelah proses cleaning.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
