<?php 
// app/Http/Controllers/ExportController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Spatie\DbDumper\Databases\MySql;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportDatabase(Request $request)
    {
        $fileName = 'kenbanb.sql';

        // Specify the path where the backup file will be stored
        $filePath = storage_path($fileName);

        MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->setDumpBinaryPath('D:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe')
            ->dumpToFile('D:\wamp64\www\kenban\backend\storage\kenbanb12.sql');

        // Provide the file for download
        return Response::download($filePath, $fileName);
    }
}
