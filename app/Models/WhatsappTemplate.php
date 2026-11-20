<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'kode',
        'nama',
        'modul',
        'judul',
        'konten',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Render konten template dengan data variabel (placeholder format {{key}})
     */
    public function render(array $data): string
    {
        $content = $this->konten;
        foreach ($data as $key => $val) {
            $val = is_scalar($val) || $val === null ? (string) $val : json_encode($val);
            $content = str_replace('{{' . $key . '}}', $val, $content);
            $content = str_replace('{{ ' . $key . ' }}', $val, $content);
        }
        return $content;
    }
}
