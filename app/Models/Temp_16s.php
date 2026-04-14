<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temp_16s extends Model
{
    protected $table = 'temp_s16_latest';

    protected $fillable = [
        'waktu',
        'sensor1',
        'sensor2',
        'sensor3',
        'sensor4',
        'sensor5',
        'sensor6',
        'sensor7',
        'sensor8',
        'sensor9',
        'sensor10',
        'sensor11',
        'sensor12',
        'sensor13',
        'sensor14',
        'sensor15',
        'sensor16',
    ];

    public function logger()
    {
        return $this->belongsTo(t_Logger::class, 'id_logger','id_logger');
    }

    /**
     * Accessor: controller menggunakan ->s1, ->s2, ->s3
     * sedangkan kolom DB bernama sensor1, sensor2, sensor3.
     */
    public function getS1Attribute(): ?float
    {
        return $this->sensor1 !== null ? (float)$this->sensor1 : null;
    }

    public function getS2Attribute(): ?float
    {
        return $this->sensor2 !== null ? (float)$this->sensor2 : null;
    }

    public function getS3Attribute(): ?float
    {
        return $this->sensor3 !== null ? (float)$this->sensor3 : null;
    }

    /**
     * Accessor: shorthand untuk sensor4, 5, 6
     * Digunakan untuk pembacaan debit aktual Flow Meter per pintu AWGC
     */
    public function getS4Attribute(): ?float
    {
        return $this->sensor4 !== null ? (float)$this->sensor4 : null;
    }

    public function getS5Attribute(): ?float
    {
        return $this->sensor5 !== null ? (float)$this->sensor5 : null;
    }

    public function getS6Attribute(): ?float
    {
        return $this->sensor6 !== null ? (float)$this->sensor6 : null;
    }
}
