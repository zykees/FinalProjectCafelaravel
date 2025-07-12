@extends('User.layouts.app')

@section('title', 'โปรไฟล์ของฉัน')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- เมนูด้านข้าง -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="{{ auth()->user()->avatar_url }}" 
                             alt="Profile Picture" 
                             class="rounded-circle img-thumbnail" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="mt-3">{{ auth()->user()->name }}</h5>
                        <p class="text-muted">สมาชิกตั้งแต่: {{ auth()->user()->created_at->format('d/m/Y') }}</p>
                    </div>
                    
                    <div class="list-group">
                        <a href="{{ route('user.profile.index') }}" 
                           class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>ข้อมูลส่วนตัว
                        </a>
                        <a href="{{ route('user.profile.edit') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-edit me-2"></i>แก้ไขข้อมูล
                        </a>
                        <a href="{{ route('user.profile.social') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-share-alt me-2"></i>เชื่อมต่อโซเชียล
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- ข้อมูลส่วนตัว -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">ข้อมูลส่วนตัว</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>ชื่อ-นามสกุล</strong>
                        </div>
                        <div class="col-md-9">
                            {{ auth()->user()->name }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>อีเมล</strong>
                        </div>
                        <div class="col-md-9">
                            {{ auth()->user()->email }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>เบอร์โทรศัพท์</strong>
                        </div>
                        <div class="col-md-9">
                            {{ auth()->user()->phone ?? 'ยังไม่ได้ระบุ' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>ที่อยู่</strong>
                        </div>
                        <div class="col-md-9">
                            {{ auth()->user()->profile->address ?? 'ยังไม่ได้ระบุ' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- สรุปการใช้งาน -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">สรุปการใช้งาน</h4>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3>{{ $bookingCount }}</h3>
                                <p>การจองทั้งหมด</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3>{{ $orderCount }}</h3>
                                <p>คำสั่งซื้อทั้งหมด</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3>฿{{ number_format($totalSpent, 2) }}</h3>
                                <p>ยอดใช้จ่ายรวม</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection