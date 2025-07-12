@extends('User.layouts.app')

@section('title', 'การจองของฉัน')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>การจองของฉัน</h2>
        <a href="{{ route('user.booking.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> จองใหม่
        </a>
    </div>

    @if($bookings->isEmpty())
        <div class="alert alert-info">
            ยังไม่มีประวัติการจอง <a href="{{ route('user.booking.create') }}">จองเลย</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัสการจอง</th>
                                <th>วันที่จอง</th>
                                <th>เวลา</th>
                                <th>จำนวนที่นั่ง</th>
                                <th>โปรโมชั่น</th>
                                <th>สถานะ</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>#{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $booking->booking_date->format('d/m/Y') }}</td>
                                <td>{{ $booking->booking_time }}</td>
                                <td>{{ $booking->number_of_guests }} ที่นั่ง</td>
                                <td>
                                    @if($booking->promotion)
                                        {{ $booking->promotion->title }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{!! $booking->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('user.bookings.show', $booking) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($booking->status === 'pending')
                                        <form action="{{ route('user.bookings.cancel', $booking) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('ยืนยันการยกเลิกการจอง?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection