<?php

namespace App\Http\Controllers\OTransaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Response;

class FolderController extends Controller
{
    protected $basePath = '\\\\192.168.0.100\\spkirim\\';

    // Tampilkan isi folder
    public function bukaFolder($folder)
    {
        $folder = basename($folder);
        $fullPath = $this->basePath . $folder;

        if (!is_dir($fullPath)) {
            return back()->with('status', "Folder $folder tidak ditemukan");
        }

        $files = array_diff(scandir($fullPath), ['.', '..']);

        return view('lihatktd', compact('files', 'folder'));
    }

    // Download file yang dipilih
    public function downloadFile(Request $request, $folder, $file)
    {
        $folder = basename($folder);
        $file = basename($file);
        $filePath = $this->basePath . $folder . '\\' . $file;

        if (!file_exists($filePath)) {
            return back()->with('status', "File $file tidak ditemukan");
        }

        return response()->download($filePath, $file);
    }
    
    // public function downloadFiles(Request $request)
    // {
    //     $folder = basename($request->folder);
    //     $selectedFiles = $request->files ?? [];

    //     if (empty($selectedFiles)) {
    //         return back()->with('status', 'Pilih minimal 1 file untuk di-download');
    //     }

    //     $zip = new ZipArchive();
    //     $zipName = 'download_' . time() . '.zip';
    //     $zipPath = sys_get_temp_dir() . '/' . $zipName;

    //     if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    //         return back()->with('status', 'Gagal membuat zip file');
    //     }

    //     foreach ($selectedFiles as $file) {
    //         $filePath = $this->basePath . $folder . '\\' . basename($file);
    //         if (file_exists($filePath)) {
    //             $zip->addFile($filePath, $file);
    //         }
    //     }

    //     $zip->close();

    //     return response()->download($zipPath)->deleteFileAfterSend(true);
    // }
}