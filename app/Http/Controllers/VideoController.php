<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * Display all videos for admin, pelatih, manajemen.
     */
    public function index()
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if ($role === 'siswa') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        
        $lagu = DB::table('lagu')
            ->where('status', 'aktif')
            ->orderBy('judul_lagu')
            ->get();
        
        $baseDir = public_path('assets/video');
        $videoData = [];
        
        foreach ($lagu as $l) {
            $dir = $baseDir . '/' . $l->id_lagu;
            if (File::exists($dir)) {
                $files = File::files($dir);
                $videos = [];
                foreach ($files as $file) {
                    if ($file->getExtension() === 'mp4') {
                        $videos[] = [
                            'name' => $file->getFilename(),
                            'path' => route('video.stream', ['id_lagu' => $l->id_lagu, 'file' => rawurlencode($file->getFilename())])
                        ];
                    }
                }
                if (!empty($videos)) {
                    $videoData[$l->id_lagu] = [
                        'judul' => $l->judul_lagu,
                        'videos' => $videos
                    ];
                }
            }
        }
        
        $isAdmin = ($role === 'admin');
        $isPelatih = ($role === 'pelatih');
        $isManajemen = ($role === 'manajemen');
        
        return view('video.index', compact('videoData', 'isAdmin', 'isPelatih', 'isManajemen', 'role'));
    }
    
    /**
     * Show upload form.
     */
    public function uploadForm()
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if (!in_array($role, ['admin', 'pelatih', 'manajemen'])) {
            return redirect()->route('video.index')->with('error', 'Akses ditolak');
        }
        
        $lagu = DB::table('lagu')
            ->where('status', 'aktif')
            ->orderBy('judul_lagu')
            ->get();
        
        return view('video.upload', compact('lagu'));
    }
    
    /**
     * Process video upload.
     */
    public function upload(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if (!in_array($role, ['admin', 'pelatih', 'manajemen'])) {
            return redirect()->route('video.index')->with('error', 'Akses ditolak');
        }
        
        $validator = Validator::make($request->all(), [
            'id_lagu' => 'required|exists:lagu,id_lagu',
            'video' => 'required|file|mimes:mp4|max:204800', // Max 200MB
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('video.upload')
                ->with('error', $validator->errors()->first())
                ->withInput();
        }
        
        try {
            $idLagu = $request->id_lagu;
            $file = $request->file('video');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            
            $destinationPath = public_path('assets/video/' . $idLagu);
            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            
            return redirect()->route('video.index')
                ->with('success', '✅ Video berhasil diupload');
                
        } catch (\Exception $e) {
            return redirect()->route('video.upload')
                ->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }
    
    /**
     * Stream video file.
     */
    public function stream(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if ($role === 'siswa') {
            abort(403, 'Akses ditolak');
        }
        
        $idLagu = $request->get('id_lagu');
        $fileName = rawurldecode((string) $request->get('file'));

        $videoPath = $this->resolveVideoPath($idLagu, $fileName);

        if (!$videoPath || !File::exists($videoPath)) {
            abort(404, 'Video tidak ditemukan');
        }
        
        return response()->file($videoPath, [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'no-cache, private',
            'Accept-Ranges' => 'bytes',
        ]);
    }
    
    /**
     * Delete video file.
     */
    public function delete(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role->nama_role ?? 'guest');
        
        if (!in_array($role, ['admin', 'pelatih', 'manajemen'])) {
            return redirect()->route('video.index')->with('error', 'Akses ditolak');
        }
        
        $validated = $request->validate([
            'id_lagu' => 'required|integer',
            'file' => 'required|string',
        ]);

        $videoPath = $this->resolveVideoPath($validated['id_lagu'], $validated['file']);

        if ($videoPath && File::exists($videoPath)) {
            File::delete($videoPath);
            return redirect()->route('video.index')->with('success', 'Video berhasil dihapus');
        }
        
        return redirect()->route('video.index')->with('error', 'Video tidak ditemukan');
    }

    private function resolveVideoPath($idLagu, string $fileName): ?string
    {
        if (!is_numeric($idLagu)) {
            return null;
        }

        $cleanName = basename($fileName);
        if ($cleanName !== $fileName) {
            return null;
        }

        if (!preg_match('/\A[a-zA-Z0-9._-]+\z/', $cleanName)) {
            return null;
        }

        $baseDir = realpath(public_path('assets/video/' . (int) $idLagu));
        if ($baseDir === false) {
            return null;
        }

        $candidatePath = $baseDir . DIRECTORY_SEPARATOR . $cleanName;
        $realPath = realpath($candidatePath);

        if ($realPath === false) {
            return null;
        }

        $baseWithSep = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realPath, $baseWithSep)) {
            return null;
        }

        return $realPath;
    }
}
