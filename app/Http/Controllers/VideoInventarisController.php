<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class VideoInventarisController extends Controller
{
    /**
     * Display a listing of video inventory.
     */
    public function index()
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if (!in_array($role, ['admin', 'pelatih', 'manajemen'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak');
        }
        
        $videoList = DB::table('video_inventaris as v')
            ->leftJoin('lagu as l', 'v.id_lagu', '=', 'l.id_lagu')
            ->select('v.*', 'l.judul_lagu')
            ->orderBy('v.urutan')
            ->orderBy('v.created_at', 'desc')
            ->get();
        
        $laguList = DB::table('lagu')->where('status', 'aktif')->orderBy('judul_lagu')->get();
        
        return view('video.inventaris', compact('videoList', 'laguList'));
    }
    
    /**
     * Store a newly created video.
     */
    public function store(Request $request)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak']);
        }
        
        $rules = [
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'id_lagu' => 'nullable|exists:lagu,id_lagu',
            'tipe' => 'required|in:upload,youtube,vimeo,googledrive,other',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ];
        
        if ($request->tipe == 'upload') {
            $rules['file_video'] = 'required|file|mimes:mp4|max:204800';
        } else {
            $rules['url_embed'] = 'required|string|max:500';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        
        try {
            $data = [
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'id_lagu' => $request->id_lagu ?: null,
                'tipe' => $request->tipe,
                'urutan' => $request->urutan ?? 0,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($request->tipe == 'upload') {
                if ($request->hasFile('file_video')) {
                    $file = $request->file('file_video');
                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                    $destinationPath = public_path('assets/video_inventaris');
                    
                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0755, true);
                    }
                    
                    $file->move($destinationPath, $filename);
                    $data['file_path'] = 'assets/video_inventaris/' . $filename;
                }
            } else {
                $data['url_embed'] = $request->url_embed;
                
                if ($request->tipe == 'youtube') {
                    if ($request->filled('youtube_id')) {
                        $data['youtube_id'] = $request->youtube_id;
                    } else {
                        $youtubeId = $this->extractYoutubeId($request->url_embed);
                        if ($youtubeId) {
                            $data['youtube_id'] = $youtubeId;
                            $data['url_embed'] = 'https://www.youtube.com/embed/' . $youtubeId;
                        }
                    }
                }
            }
            
            DB::table('video_inventaris')->insert($data);
            
            return response()->json(['success' => true, 'message' => '✅ Video berhasil ditambahkan']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update the specified video.
     */
    public function update(Request $request, $id)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak']);
        }
        
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'id_lagu' => 'nullable|exists:lagu,id_lagu',
            'tipe' => 'required|in:upload,youtube,vimeo,googledrive,other',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        
        try {
            $data = [
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'id_lagu' => $request->id_lagu ?: null,
                'tipe' => $request->tipe,
                'urutan' => $request->urutan ?? 0,
                'status' => $request->status,
                'updated_at' => now(),
            ];
            
            if ($request->tipe == 'upload' && $request->hasFile('file_video')) {
                $old = DB::table('video_inventaris')->where('id_video', $id)->first();
                if ($old && $old->file_path && File::exists(public_path($old->file_path))) {
                    File::delete(public_path($old->file_path));
                }
                
                $file = $request->file('file_video');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $destinationPath = public_path('assets/video_inventaris');
                
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                
                $file->move($destinationPath, $filename);
                $data['file_path'] = 'assets/video_inventaris/' . $filename;
            }
            
            if ($request->tipe != 'upload') {
                $data['url_embed'] = $request->url_embed;
                if ($request->tipe == 'youtube') {
                    $data['youtube_id'] = $request->youtube_id;
                    if (empty($request->url_embed)) {
                        $data['url_embed'] = 'https://www.youtube.com/embed/' . $request->youtube_id;
                    }
                }
            }
            
            DB::table('video_inventaris')->where('id_video', $id)->update($data);
            
            return response()->json(['success' => true, 'message' => '✏️ Video berhasil diperbarui']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Remove the specified video.
     */
    public function destroy(Request $request, $id)
    {
        $role = strtolower(auth()->user()->role->nama_role ?? 'guest');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak']);
        }
        
        try {
            $video = DB::table('video_inventaris')->where('id_video', $id)->first();
            
            if ($video && $video->file_path && File::exists(public_path($video->file_path))) {
                File::delete(public_path($video->file_path));
            }
            
            DB::table('video_inventaris')->where('id_video', $id)->delete();
            
            return response()->json(['success' => true, 'message' => '🗑️ Video berhasil dihapus']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get video data for edit (AJAX).
     */
    public function getVideo($id)
    {
        $video = DB::table('video_inventaris')->where('id_video', $id)->first();
        
        if ($video) {
            return response()->json(['success' => true, 'data' => $video]);
        }
        
        return response()->json(['success' => false, 'message' => 'Video tidak ditemukan']);
    }
    
    /**
     * Play video.
     */
    public function player($id)
    {
        $video = DB::table('video_inventaris')->where('id_video', $id)->where('status', 'aktif')->first();
        
        if (!$video) {
            abort(404, 'Video tidak ditemukan');
        }
        
        $relatedVideos = DB::table('video_inventaris')
            ->where('status', 'aktif')
            ->where('id_video', '!=', $id)
            ->orderBy('urutan')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
        
        return view('video.player', compact('video', 'relatedVideos'));
    }
    
    /**
     * Extract YouTube ID from URL.
     */
    private function extractYoutubeId($url)
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=)([^&]+)/',
            '/(?:youtu\.be\/)([^?]+)/',
            '/(?:youtube\.com\/embed\/)([^?]+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}