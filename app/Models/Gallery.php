<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
    ];

    // Accessor สำหรับดึง URL รูปจาก Cloudinary
    public function getImageUrl(): string
    {
        if (!empty($this->image)) {
            // Cloudinary public_id จะได้ url แบบนี้
            return 'https://res.cloudinary.com/' . env('CLOUDINARY_CLOUD_NAME') . '/image/upload/' . $this->image;
        }
        return asset('images/no-image.png');
    }
    public function getStatusColor(): string
{
    return match ($this->status) {
        'active' => 'success',    // ตัวอย่าง: ใช้สีเขียว
        'inactive' => 'secondary',// ตัวอย่าง: ใช้สีเทา
        default => 'light',       // ตัวอย่าง: สีอ่อน
    };
}
}