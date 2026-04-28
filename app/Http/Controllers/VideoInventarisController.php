<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class VideoInventarisController extends Controller
{
    /**
     * LIST VIDEO (ROLE-BASED SAFE)
     */
    public function index()
{
    $user = auth()->user();

    $role = strtolower(optional($user->role)->nama_role ?? '');

    $isAdmin = ($role === 'admin');

    // 🔥 SEMUA ROLE LIHAT DATA YANG SAMA
    $videoList = DB::table('video_inventaris as v')
        ->leftJoin('lagu as l', 'v.id_lagu', '=', 'l.id_lagu')
        ->select('v.*', 'l.judul_lagu')
        ->orderBy('v.urutan')
        ->orderBy('v.created_at', 'desc')
        ->get();

    $laguList = DB::table('lagu')
        ->where('status', 'aktif')
        ->orderBy('judul_lagu')
        ->get();

    // 🔥 BEDANYA HANYA DI VIEW
    if ($isAdmin) {
        return view('video.inventaris', compact('videoList', 'laguList'));
    } else {
        return view('video.inventaris-readonly', compact('videoList'));
    }
}

    /**
     * STORE VIDEO (ADMIN ONLY)
     */
    public function store(Request $request)
    {
        $role = strtolower(optional(auth()->user()->role)->nama_role ?? '');

        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $rules = [
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'id_lagu' => 'nullable|exists:lagu,id_lagu',
            'id_kelas' => 'nullable|integer',
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
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $data = [
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'id_lagu' => $request->id_lagu ?: null,
                'id_kelas' => $request->id_kelas ?: null,
                'tipe' => $request->tipe,
                'urutan' => $request->urutan ?? 0,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($request->tipe == 'upload' && $request->hasFile('file_video')) {

                $file = $request->file('file_video');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());

                $path = public_path('assets/video_inventaris');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                $file->move($path, $filename);
                $data['file_path'] = 'assets/video_inventaris/' . $filename;

            } else {

                $data['url_embed'] = $request->url_embed;

                if ($request->tipe == 'youtube') {
                    $youtubeId = $this->extractYoutubeId($request->url_embed);
                    if ($youtubeId) {
                        $data['youtube_id'] = $youtubeId;
                        $data['url_embed'] = 'https://www.youtube.com/embed/' . $youtubeId;
                    }
                }
            }

            DB::table('video_inventaris')->insert($data);

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil ditambahkan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage()
            ]);
        }
    }

    /**
     * UPDATE VIDEO (ADMIN ONLY)
     */
    public function update(Request $request, $id)
    {
        $role = strtolower(optional(auth()->user()->role)->nama_role ?? '');

        if ($role !== 'admin') {
            return response()->json(['success' => false], 403);
        }

        $data = $request->only([
            'judul','deskripsi','id_lagu','id_kelas','tipe','urutan','status'
        ]);

        DB::table('video_inventaris')->where('id_video', $id)->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * DELETE VIDEO (ADMIN ONLY)
     */
    public function destroy($id)
    {
        $role = strtolower(optional(auth()->user()->role)->nama_role ?? '');

        if ($role !== 'admin') {
            return response()->json(['success' => false], 403);
        }

        $video = DB::table('video_inventaris')->where('id_video', $id)->first();

        if ($video && $video->file_path && File::exists(public_path($video->file_path))) {
            File::delete(public_path($video->file_path));
        }

        DB::table('video_inventaris')->where('id_video', $id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * PLAYER (AMAN UNTUK SISWA)
     */
    public function player($id)
    {
        $user = auth()->user();
        $role = strtolower(optional($user->role)->nama_role ?? '');

        $query = DB::table('video_inventaris')
            ->where('id_video', $id)
            ->where('status', 'aktif');

        if ($role === 'siswa') {
            $query->join('kelas_siswa as ks', 'ks.id_kelas', '=', 'video_inventaris.id_kelas')
                  ->where('ks.id_user', $user->id_user);
        }

        $video = $query->first();

        if (!$video) {
            abort(404);
        }

        $relatedVideos = DB::table('video_inventaris')
            ->where('status', 'aktif')
            ->where('id_video', '!=', $id)
            ->limit(6)
            ->get();

        return view('video.player', compact('video', 'relatedVideos'));
    }

    /**
     * YOUTUBE HELPER
     */
    private function extractYoutubeId($url)
    {
        preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches);
        return $matches[2] ?? null;
    }
}