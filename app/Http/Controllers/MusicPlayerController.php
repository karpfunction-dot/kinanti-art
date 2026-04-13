<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class MusicPlayerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if ($role === 'siswa') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        
        $basePath = public_path('assets/musik/');
        $kelasList = [];
        
        if (File::exists($basePath)) {
            $folders = File::directories($basePath);
            
            foreach ($folders as $folder) {
                $kelasName = basename($folder);
                $kelasList[$kelasName] = [];
                
                $files = File::files($folder);
                foreach ($files as $file) {
                    $extension = $file->getExtension();
                    if (in_array($extension, ['mp3', 'wav', 'ogg', 'mp4', 'm4a'])) {
                        $kelasList[$kelasName][] = [
                            'name' => $file->getFilename(),
                            'extension' => $extension
                        ];
                    }
                }
            }
        }
        
        // Sort kelas berdasarkan nama (1,2,3,4,5)
        ksort($kelasList);
        
        return view('music-player.index', compact('kelasList', 'role'));
    }
}