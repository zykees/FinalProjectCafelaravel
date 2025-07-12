<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ปรับ timezone สำหรับตาราง orders
        DB::statement('ALTER TABLE orders MODIFY created_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE orders MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');
        
        // ปรับ timezone สำหรับตาราง bookings
        DB::statement('ALTER TABLE bookings MODIFY created_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE bookings MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');
        
        // ปรับ timezone สำหรับตาราง promotions
        DB::statement('ALTER TABLE promotions MODIFY created_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE promotions MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE promotions MODIFY starts_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE promotions MODIFY ends_at TIMESTAMP NULL DEFAULT NULL');
        
        // อัพเดตข้อมูลที่มีอยู่เป็น timezone ไทย
        DB::statement("UPDATE orders SET created_at = CONVERT_TZ(created_at, 'UTC', 'Asia/Bangkok') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE orders SET updated_at = CONVERT_TZ(updated_at, 'UTC', 'Asia/Bangkok') WHERE updated_at IS NOT NULL");
        
        DB::statement("UPDATE bookings SET created_at = CONVERT_TZ(created_at, 'UTC', 'Asia/Bangkok') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE bookings SET updated_at = CONVERT_TZ(updated_at, 'UTC', 'Asia/Bangkok') WHERE updated_at IS NOT NULL");
        
        DB::statement("UPDATE promotions SET created_at = CONVERT_TZ(created_at, 'UTC', 'Asia/Bangkok') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE promotions SET updated_at = CONVERT_TZ(updated_at, 'UTC', 'Asia/Bangkok') WHERE updated_at IS NOT NULL");
        DB::statement("UPDATE promotions SET starts_at = CONVERT_TZ(starts_at, 'UTC', 'Asia/Bangkok') WHERE starts_at IS NOT NULL");
        DB::statement("UPDATE promotions SET ends_at = CONVERT_TZ(ends_at, 'UTC', 'Asia/Bangkok') WHERE ends_at IS NOT NULL");
    }

    public function down()
    {
        // แปลงกลับเป็น UTC ถ้าต้องการ rollback
        DB::statement("UPDATE orders SET created_at = CONVERT_TZ(created_at, 'Asia/Bangkok', 'UTC') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE orders SET updated_at = CONVERT_TZ(updated_at, 'Asia/Bangkok', 'UTC') WHERE updated_at IS NOT NULL");
        
        DB::statement("UPDATE bookings SET created_at = CONVERT_TZ(created_at, 'Asia/Bangkok', 'UTC') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE bookings SET updated_at = CONVERT_TZ(updated_at, 'Asia/Bangkok', 'UTC') WHERE updated_at IS NOT NULL");
        
        DB::statement("UPDATE promotions SET created_at = CONVERT_TZ(created_at, 'Asia/Bangkok', 'UTC') WHERE created_at IS NOT NULL");
        DB::statement("UPDATE promotions SET updated_at = CONVERT_TZ(updated_at, 'Asia/Bangkok', 'UTC') WHERE updated_at IS NOT NULL");
        DB::statement("UPDATE promotions SET starts_at = CONVERT_TZ(starts_at, 'Asia/Bangkok', 'UTC') WHERE starts_at IS NOT NULL");
        DB::statement("UPDATE promotions SET ends_at = CONVERT_TZ(ends_at, 'Asia/Bangkok', 'UTC') WHERE ends_at IS NOT NULL");
    }
};