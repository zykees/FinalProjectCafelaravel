
<footer class="bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">เกี่ยวกับเรา</h5>
                <p>ร้านคาเฟ่สำหรับครอบครัว ที่มอบประสบการณ์ความสุขและความอร่อยให้กับทุกคน</p>
                <div class="social-links">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-line"></i></a>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">ติดต่อเรา</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> 123 ถนนสุขุมวิท กรุงเทพฯ 10110</p>
                <p><i class="fas fa-phone me-2"></i> 02-123-4567</p>
                <p><i class="fas fa-envelope me-2"></i> contact@example.com</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">เวลาทำการ</h5>
                <p>จันทร์ - ศุกร์: 10:00 - 20:00</p>
                <p>เสาร์ - อาทิตย์: 09:00 - 21:00</p>
                <p>วันหยุดนักขัตฤกษ์: 09:00 - 21:00</p>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="{{ route('user.pages.privacy') }}" class="text-light me-3">นโยบายความเป็นส่วนตัว</a>
                <a href="{{ route('user.pages.terms') }}" class="text-light">ข้อกำหนดและเงื่อนไข</a>
            </div>
        </div>
    </div>
</footer>