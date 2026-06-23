<?php

namespace App\Models;

use App\Support\SensorFamily;
use Illuminate\Database\Eloquent\Model;

class t_Logger extends Model
{
    protected $table = 't_logger';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_logger',
        'instansi_id',
        'nama_logger',
        'tabel_main',
        'jeda_notif',
        'idlokasi',
        'id_kategori',
        'sensor_count',
        'status_perbaikan',
        // Kolom baru untuk integrasi Skema Irigasi AWLR/AWGC
        'jenis_alat',         // AWLR | AWGC | OTHER
        'node_skema_id',      // ID node di SVG skema (mis: 'BGP_1', 'WEIR_COPONG')
        'bukaan_maksimal_cm', // Batas maksimal bukaan pintu AWGC (cm)
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id');
    }

    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : ($user->level_user === 'superadmin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // instansi_admin melihat semua logger di instansinya sendiri.
            if (method_exists($user, 'isInstansiAdmin') && $user->isInstansiAdmin()) {
                $q->where('instansi_id', $user->instansi_id);
            }

            // Semua non-superadmin: tambah logger yang diberikan eksplisit
            // lewat pivot (boleh lintas instansi).
            $q->orWhereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('user_logger_access as ula')
                    ->whereColumn('ula.logger_id', 't_logger.id_logger')
                    ->where('ula.user_id', $user->id_user);
            });
        });
    }

    public function lokasi()
    {
        return $this->belongsTo(t_Lokasi::class, 'idlokasi', 'idlokasi');
    }

    /**
     * Nama pos yang ditampilkan ke pengguna: selalu ambil dari t_lokasi
     * (nama_lokasi). Fallback ke nama_logger hanya bila lokasi belum diisi,
     * supaya judul pos tidak pernah kosong.
     */
    public function getNamaPosAttribute()
    {
        return $this->lokasi?->nama_lokasi ?: $this->nama_logger;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori_logger::class, 'id_katlogger', 'id_katlogger');
    }

    public function allowedUsers()
    {
        return $this->belongsToMany(
            t_User::class,
            'user_logger_access',
            'logger_id',
            'user_id',
            'id_logger',
            'id_user'
        );
    }

    public function params()
    {
        return $this->hasMany(Parameter::class, 'logger_id', 'id_logger');
    }

    public function jiat()
    {
        return $this->hasOne(Jiat_data::class, 'id_logger', 'id_logger');
    }

    public function nonjiat()
    {
        return $this->hasOne(NonJiatData::class, 'id_logger', 'id_logger');
    }

    public function afmrContact()
    {
        return $this->hasOne(AfmrContactData::class, 'id_logger', 'id_logger');
    }

    public function afmrNonContact()
    {
        return $this->hasOne(AfmrNonContactData::class, 'id_logger', 'id_logger');
    }

    public function temp19()
    {
        return $this->hasOne(Temp_19s::class, 'id_logger', 'id_logger');
    }

    public function temp16()
    {
        return $this->hasOne(Temp_16s::class, 'id_logger', 'id_logger');
    }

    public function temp50()
    {
        return $this->hasOne(Temp_50s::class, 'id_logger', 'id_logger');
    }

    public function getTempAttribute()
    {
        return match (SensorFamily::familyFor((int) $this->sensor_count)) {
            50 => $this->temp50,
            19 => $this->temp19,
            default => $this->temp16,
        };
    }

    public function informasi()
    {
        return $this->hasOne(t_Informasi::class, 'logger_id', 'id_logger')->latest('id_inf');
    }

    public function fotos()
    {
        return $this->hasMany(Foto_pos::class, 'id_logger', 'id_logger');
    }

    public function s16data()
    {
        return $this->hasMany(T_s16::class, 'id_logger', 'id_logger');
    }

    public function s19data()
    {
        return $this->hasMany(T_s19::class, 'id_logger', 'id_logger');
    }

    public function s50data()
    {
        return $this->hasMany(T_s50::class, 'id_logger', 'id_logger');
    }

    public function klasifikasiHujan()
    {
        return $this->hasOne(Klasifikasi_hujan::class, 'logger_id', 'id_logger');
    }

    public function tingkatSiagaAwlr()
    {
        return $this->hasMany(TingkatSiagaAwlr::class, 'id_logger', 'id_logger');
    }

    public function perbaikan()
    {
        return $this->hasMany(Perbaikan::class, 'id_logger', 'id_logger')->latest();
    }

    /**
     * Log historis perintah AWGC yang diberikan ke logger pintu air ini.
     * Hanya relevan jika jenis_alat = 'AWGC'.
     */
    public function awgcCommandLogs()
    {
        return $this->hasMany(\App\Models\AwgcCommandLog::class, 'id_logger', 'id_logger')->latest();
    }

    /**
     * Scope: Filter hanya logger bertipe AWLR.
     */
    public function scopeAwlr($query)
    {
        return $query->where('jenis_alat', 'AWLR');
    }

    /**
     * Scope: Filter hanya logger bertipe AWGC.
     */
    public function scopeAwgc($query)
    {
        return $query->where('jenis_alat', 'AWGC');
    }

    /**
     * Scope: Filter logger yang terhubung ke skema irigasi (ada node_skema_id).
     */
    public function scopeLinkedToSkema($query)
    {
        return $query->whereNotNull('node_skema_id');
    }
}
