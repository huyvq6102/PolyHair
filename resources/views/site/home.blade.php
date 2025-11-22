@extends('layouts.site')

@section('title', $settings->title ?? 'Trang chủ')

@section('content')
    @include('site.partials.slider')

    <!--1 MẪU TÓC HOT -->

<section class="album-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">MẪU TÓC HOT</h3>
        <p class="desc">
            Chào mừng Quý khách hàng đến với Traky Hair Salon,
            nơi mang đến cho bạn trải nghiệm làm đẹp tinh tế và độc đáo.
            Dưới đây là bộ sưu tập những mẫu tóc đẹp nhất năm 2024 giúp nâng tầm vẻ đẹp của bạn lên một tầm cao mới.
        </p>
      </div>
    </div>
    <div class="album-grid">
      @foreach([
        ['name'=>'MÀU TRENDY','link'=>'#','img'=>'https://trakyhairsalon.com/watermark/product/300x450x1/upload/product/300x450-1-7558.png'],
        ['name'=>'KIỂU TÓC HOT','link'=>'#','img'=>'https://trakyhairsalon.com/watermark/product/300x450x1/upload/product/300x450-3-1088.png'],
        ['name'=>'TÓC LỠ','link'=>'#','img'=>'https://trakyhairsalon.com/watermark/product/300x450x1/upload/product/300x450-4-2010.png'],
        ['name'=>'TÓC LỠ','link'=>'#','img'=>'https://trakyhairsalon.com/watermark/product/300x450x1/upload/product/300x450-4-2010.png'],
      ] as $item)
      <div class="album-card">
        <div class="album-img"><img src="{{ $item['img'] }}" alt="{{ $item['name'] }}"></div>
        <div class="album-name">{{ $item['name'] }}</div>
        <a class="album-link" href="{{ $item['link'] }}">Xem thêm</a>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-3"><a class="btn-view-all" href="#">Xem tất cả</a></div>
  </div>
</section>

    <!--2 DỊCH VỤ TÓC -->
<section class="service-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="bar mr-2"></span>
      <div>
        <h3 class="title ba-title mb-0">DỊCH VỤ TÓC</h3>
        <p class="desc">
          Một số dịch vụ làm tóc bên salon chúng tôi hiện nay mà bạn quan tâm
        </p>
      </div>
    </div>
  </div>
  <div class="container service-wrapper">
    <div class="service_right">

      <div class="service-grid">
        @foreach([
          ['name'=>'NỐI TÓC','price'=>'15.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-5-7605.png'],
          ['name'=>'PHỤC HỒI','price'=>'300.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-3-4269.png'],
          ['name'=>'CẮT TÓC','price'=>'200.000vnđ','link'=>'#','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/375-x-500-2-2717.png'],
          
        ] as $svc)
        <div class="svc-card">
          <a class="svc-img" href="{{ $svc['link'] }}"><img src="{{ $svc['img'] }}" alt="{{ $svc['name'] }}"></a>
          <div class="svc-body">
            <div class="svc-left">
              <h4 class="svc-name">{{ $svc['name'] }}</h4>
              <div class="svc-price">Giá từ: <span>{{ $svc['price'] }}</span></div>
            </div>
            <div class="svc-right">
              <span class="svc-rating">5 ★ Đánh giá</span>
              <a class="svc-book" href="{{ $svc['link'] }}">Đặt lịch ngay</a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      <div class="text-center mt-3"><a class="btn-view-all" href="{{ route('site.services.index') }}">Xem tất cả</a></div>
    </div>
  </div>
</section>

    <!--3 khuyen mai service_area_start -->

    <section class="promo-section py-5">
    <div class="container">
        <div class="d-flex align-items-center mb-3">
            <span class="promo-bar mr-2"></span>
            <h3 class="promo-title mb-0">CHƯƠNG TRÌNH KHUYẾN MÃI</h3>
        </div>
        <div class="promo-slider">
            <button class="promo-nav prev" aria-label="Prev">‹</button>
            <div class="promo-viewport">
                <div class="promo-track">
                    @foreach([
                        [
                            'title' => 'Khuyến mãi đặc biệt mùa hè',
                            'desc'  => 'Giảm ngay 20% cho tất cả dịch vụ làm tóc',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/2.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho khách hàng mới',
                            'desc'  => 'Giảm giá 15% cho lần đầu sử dụng dịch vụ',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/3.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho dịch vụ chăm sóc tóc',
                            'desc'  => 'Combo chăm sóc, phục hồi, dưỡng bóng mượt…',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/1.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho dịch vụ chăm sóc tóc',
                            'desc'  => 'Combo chăm sóc, phục hồi, dưỡng bóng mượt…',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/1.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho dịch vụ chăm sóc tóc',
                            'desc'  => 'Combo chăm sóc, phục hồi, dưỡng bóng mượt…',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/1.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho dịch vụ chăm sóc tóc',
                            'desc'  => 'Combo chăm sóc, phục hồi, dưỡng bóng mượt…',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/1.png',
                        ],
                        [
                            'title' => 'Ưu đãi hấp dẫn cho dịch vụ chăm sóc tóc',
                            'desc'  => 'Combo chăm sóc, phục hồi, dưỡng bóng mượt…',
                            'link'  => '#',
                            'img'   => 'legacy/images/sliders/1.png',
                        ],
                    ] as $promo)
                    <a class="news__items promo-card" href="{{ $promo['link'] }}" title="{{ $promo['title'] }}">
                        <div class="news__img img_hover scale-img promo-img">
                            <img class="w-100" src="{{ asset($promo['img']) }}" alt="{{ $promo['title'] }}">
                        </div>
                        <div class="news__txt promo-body">
                            <h3 class="news__name promo-heading"><span>{{ $promo['title'] }}</span></h3>
                            <div class="news__line"></div>
                            <div class="news__desc"><span>{{ $promo['desc'] }}</span></div>
                            <div class="news__link"><i>/ Xem chi tiết</i><span>+</span></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            <button class="promo-nav next" aria-label="Next">›</button>
        </div>
    </div>
</section>
    <!-- khuyen mai service_area_end -->

    <!-- 4 FEEDBACK KHÁCH HÀNG -->
<section class="feedback-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="fb-bar mr-2"></span>
      <div>
        <h3 class="fb-title mb-1 ba-title mb-0">FEEDBACK KHÁCH HÀNG</h3>
        <p class="fb-desc mb-0">Dưới đây là những chia sẻ và cảm nhận của khách hàng khi sử dụng dịch vụ của Traky.</p>
      </div>
    </div>

    <div class="fb-slider">
      <button class="fb-nav prev" aria-label="Prev">‹</button>
      <div class="fb-viewport">
        <div class="fb-track">
          @foreach([
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/4521855211921800244922583292012091870822199n-65270.jpg',
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/45429722539776299825202751161716994539239682n-31370.jpg',
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/46732587315178433255834577210241778246238031n-18140.jpg',
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/4613427028548217134619082361666114740470727n-49950.jpg',
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/46667694715145918325752731143632095580929221n-11060.jpg',
            'https://trakyhairsalon.com/thumbs/500x545x1/upload/photo/1ba9680e30848adad395-66330.jpg',
          ] as $img)
          <a class="fb-card" href="#">
            <div class="fb-img"><img src="{{ $img }}" alt="Feedback"></div>
          </a>
          @endforeach
        </div>
      </div>
      <button class="fb-nav next" aria-label="Next">›</button>
    </div>

    <div class="text-center mt-3">
      <a class="btn-view-all" href="#">Xem tất cả</a>
    </div>
  </div>
</section>

<!-- BEFORE & AFTER -->
 <section class="before-after-section py-5">
    <div class="container">
        <div class="d-flex align-items-center mb-3">
            <span class="ba-bar mr-2"></span>
            <h3 class="ba-title mb-0">BEFORE & AFTER</h3>
        </div>
        <p class="text-muted mb-4">
            Chúng ta thường xuyên nghe về sức mạnh của một kiểu tóc đẹp... trải nghiệm sự thay đổi tại đây.
        </p>

        <div class="ba-slider">
            <button class="ba-nav prev" aria-label="Prev">‹</button>
            <div class="ba-viewport">
                <div class="ba-track">
                    @foreach([
                        ['title'=>'Kiểu Tóc Hush Cut','img'=>'https://trakyhairsalon.com/thumbs/390x660x1/upload/photo/63912d7021a085fedcb1-7478.jpg','video'=>'https://www.youtube.com/watch?v=5VOz__vyEhs'],
                        ['title'=>'Nhuộm Tóc + Uốn Xoăn Lơi','img'=>'https://trakyhairsalon.com/thumbs/390x660x1/upload/photo/9669d0c3f513514d0802-1635.jpg','video'=>'https://www.youtube.com/watch?v=p87x50eGm8E'],
                        ['title'=>'Trước Và Sau Khi Nối Tóc','img'=>'https://trakyhairsalon.com/thumbs/390x660x1/upload/photo/29d5326a98bb3ce565aa-9602.jpg','video'=>'https://www.youtube.com/watch?v=woF99xC4qkM'],
                        ['title'=>'Nối Tóc Lông Vũ','img'=>'https://trakyhairsalon.com/thumbs/390x660x1/upload/photo/cecf2787b7f213ac4ae3-1-6731.jpg','video'=>'https://www.youtube.com/watch?v=nqgvBN-PBwM'],
                    ] as $item)
                    <div class="ba-card" data-video="{{ $item['video'] }}">
                        <div class="ba-img">
                            <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}">
                        </div>
                        <div class="ba-name">{{ $item['title'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <button class="ba-nav next" aria-label="Next">›</button>
        </div>

        <div class="text-center mt-4">
            <a href="#" class="btn-view-all">Xem tất cả</a>
        </div>
    </div>
</section>

<!-- ETRAKY’S STYLIST -->
 <section class="stylist-section py-5">
    <div class="container stylist-wrapper">
        <div class="stylist-left">
            <!-- <div class="stylist-letter">T</div> -->
            <h2 class="stylist-title ba-title mb-0">TRAKY’S STYLIST</h2>
            <p class="stylist-desc">
                Chúng tôi tự hào sở hữu một đội ngũ nghệ sĩ tóc tài năng và có kinh nghiệm. Với sự đam mê sáng tạo
                và kiến thức chuyên sâu về xu hướng làm đẹp, họ không chỉ biến ý tưởng của bạn thành hiện thực
                mà còn mang lại sự tự tin và phong cách mới cho vẻ ngoại hình của bạn.
            </p>
        </div>

        <div class="stylist-right">
            <div class="stylist-grid">
                @foreach([
                    ['name'=>'Hùng Phạm','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/dsc01646-88180.jpg'],
                    ['name'=>'Tuấn Anh','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/e21400bd6a6ece30977f-11410.jpg'],
                    ['name'=>'Phương Vũ','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/c2ddd31d-58b8-4d17-9263-a94a60c4f0ac-1616.jpeg'],
                    ['name'=>'Duy Khánh','img'=>'https://trakyhairsalon.com/thumbs/375x500x1/upload/news/dsc01326-1-8054.jpg'],
                ] as $sty)
                <div class="stylist-card">
                    <div class="stylist-img">
                        <img src="{{ $sty['img'] }}" alt="{{ $sty['name'] }}">
                    </div>
                    <div class="stylist-meta">
                        <h3 class="stylist-name">{{ $sty['name'] }}</h3>
                        <a href="#" class="stylist-book">Đặt lịch ngay</a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-3">
                <a href="#" class="btn-view-all">Xem tất cả</a>
            </div>
        </div>
    </div>
</section>

<!-- END TRAKY’S STYLIST -->
<!-- San phảm chăm sóc tóc -->
<section class="product-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="prod-bar mr-2"></span>
      <div>
        <h3 class="prod-title mb-1 ba-title mb-0">SẢN PHẨM CHĂM SÓC TÓC</h3>
        <p class="prod-desc mb-0">Chúng tôi cam kết đảm bảo sức khỏe và vẻ đẹp tự nhiên của tóc bạn thông qua những sản phẩm chăm sóc tóc chất lượng cao và tiên tiến.</p>
      </div>
    </div>

    <div class="prod-criteria mb-3">
      @foreach([
        ['name'=>'Giao Hàng Hỏa Tốc','desc'=>'Nhận hàng nhanh chóng trong thời gian ngắn nhất.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc1-42290.png'],
        ['name'=>'Hoàn Tiền 100%','desc'=>'Cam kết hoàn tiền nếu sản phẩm không đạt yêu cầu.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc2-14340.png'],
        ['name'=>'Chính Sách Đổi Trả','desc'=>'Đổi trả dễ dàng và nhanh chóng.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc3-80241.png'],
        ['name'=>'Cam Kết Chính Hãng','desc'=>'Sản phẩm 100% chính hãng, xuất xứ rõ ràng.','img'=>'https://trakyhairsalon.com/thumbs/60x60x2/upload/photo/tc4-60532.png'],
      ] as $tc)
      <div class="tc-card">
        <div class="tc-img"><img src="{{ $tc['img'] }}" alt="{{ $tc['name'] }}"></div>
        <div class="tc-text">
          <div class="tc-name">{{ $tc['name'] }}</div>
          <div class="tc-desc">{{ $tc['desc'] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
<!-- End San phảm chăm sóc tóc -->
 <!-- hệ thống salon -->
  <section class="salon-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="salon-bar mr-2"></span>
      <div>
        <h3 class="salon-title mb-1 ba-title mb-0">HỆ THỐNG TRAKY HAIR SALON</h3>
        <p class="salon-desc mb-0">Hệ thống TRAKY HAIR SALON, đang có hơn 40 chi nhánh trên toàn quốc!</p>
      </div>
    </div>

    <div class="salon-slider">
      <button class="salon-nav prev" aria-label="Prev">‹</button>
      <div class="salon-viewport">
        <div class="salon-track">
          @foreach([
            ['name'=>'TP. Hồ Chí Minh','desc'=>'9 salon','img'=>'https://trakyhairsalon.com/thumbs/300x225x1/upload/news/cn1-3166.jpg','link'=>'#'],
            ['name'=>'TP. Hà Nội','desc'=>'1 salon','img'=>'https://trakyhairsalon.com/thumbs/300x225x1/upload/news/cn1-7398.jpg','link'=>'#'],
            ['name'=>'Kiên Giang','desc'=>'2 salon','img'=>'https://trakyhairsalon.com/thumbs/300x225x1/upload/news/231201fbc6306d6e34215-2756.jpg','link'=>'#'],
            ['name'=>'Đà Nẵng','desc'=>'2 salon','img'=>'https://trakyhairsalon.com/thumbs/300x225x1/upload/news/cn1-3697.jpg','link'=>'#'],
            ['name'=>'Các thành phố khác','desc'=>'22 salon','img'=>'https://trakyhairsalon.com/thumbs/300x225x1/upload/news/a6ad6db5aa7e0120586f4-8616.jpg','link'=>'#'],
          ] as $item)
            <a class="salon-card" href="{{ $item['link'] }}">
              <div class="salon-img"><img src="{{ $item['img'] }}" alt="{{ $item['name'] }}"></div>
              <div class="salon-txt">
                <div class="salon-name">{{ $item['name'] }}</div>
                <div class="salon-desc2">{{ $item['desc'] }}</div>
              </div>
            </a>
          @endforeach
        </div>
      </div>
      <button class="salon-nav next" aria-label="Next">›</button>
    </div>
  </div>
</section>
    <!-- end hệ thống salon -->
<!-- đối tác -->
 <section class="partner-section py-5">
  <div class="container">
    <div class="d-flex align-items-start mb-3">
      <span class="partner-bar mr-2"></span>
      <h3 class="partner-title mb-0 ba-title mb-0">ĐỐI TÁC</h3>
    </div>

    <div class="partner-slider">
      <button class="partner-nav prev" aria-label="Prev">‹</button>
      <div class="partner-viewport">
        <div class="partner-track">
          @foreach([
            ['name'=>'OLAPLEX','desc'=>'Olaplex là một trong những thương hiệu chăm sóc tóc lớn nhất...','img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-7253.png'],
            ['name'=>'MOROCCANOIL','desc'=>'Moroccanoil là thương hiệu chăm sóc tóc nổi tiếng toàn cầu...','img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-1-5586.png'],
            ['name'=>'B3 BRAZILIAN','desc'=>'B3 Brazillian Bond Builder nổi tiếng hàng đầu tại Mỹ...','img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-2-6250.png'],
            ['name'=>"L'Oréal",'desc'=>"L'Oréal Paris là thương hiệu mỹ phẩm hàng đầu thế giới...",'img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-3-3370.png'],
                        ['name'=>"L'Oréal",'desc'=>"L'Oréal Paris là thương hiệu mỹ phẩm hàng đầu thế giới...",'img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-3-3370.png'],
                        ['name'=>"L'Oréal",'desc'=>"L'Oréal Paris là thương hiệu mỹ phẩm hàng đầu thế giới...",'img'=>'https://trakyhairsalon.com/thumbs/300x300x2/upload/photo/300x300-3-3370.png'],
          ] as $p)
          <a class="partner-card" href="#">
            <div class="partner-img"><img src="{{ $p['img'] }}" alt="{{ $p['name'] }}"></div>
            <div class="partner-txt">
              <h4 class="partner-name">{{ $p['name'] }}</h4>
              <div class="partner-desc">{{ $p['desc'] }}</div>
            </div>
          </a>
          @endforeach
        </div>
      </div>
      <button class="partner-nav next" aria-label="Next">›</button>
    </div>
  </div>
</section>
    <!-- end đối tác -->
    <!-- cộng đồng -->
     <section class="community-section py-4">
  <div class="container">
    <div class="community-slider">
      <div class="comm-viewport">
        <div class="comm-track">
          @foreach([
            ['name'=>'Fanpage','count'=>'178.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/facebook-84181.png'],
            ['name'=>'Zalo','count'=>'79.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/zalo-82972.png'],
            ['name'=>'Tiktok channel','count'=>'634.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/tiktok-50110.png'],
            ['name'=>'Instagram','count'=>'140.000+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/instagram-89822.png'],
            ['name'=>'Chi nhánh','count'=>'63+','img'=>'https://trakyhairsalon.com/thumbs/100x100x2/upload/photo/maps-67360.png'],
          ] as $cd)
          <div class="comm-card">
            <div class="comm-img"><img src="{{ $cd['img'] }}" alt="{{ $cd['name'] }}"></div>
            <div class="comm-desc">{{ $cd['count'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
    <!-- end cộng đồng -->

@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.promo-track');
    const cards = Array.from(track.children);
    let index = 0;

    function cardWidth() {
        const card = cards[0];
        return card ? card.getBoundingClientRect().width + 28 /*gap*/ : 0;
    }
    function maxIndex() {
        const vw = document.querySelector('.promo-viewport').clientWidth;
        const visible = Math.max(1, Math.floor(vw / cardWidth()));
        return Math.max(0, cards.length - visible);
    }
    function render() {
        const offset = index * cardWidth();
        track.style.transform = `translateX(-${offset}px)`;
    }

    document.querySelector('.promo-nav.prev').onclick = () => {
        index = Math.max(0, index - 1); render();
    };
    document.querySelector('.promo-nav.next').onclick = () => {
        index = Math.min(maxIndex(), index + 1); render();
    };
    window.addEventListener('resize', () => { index = Math.min(index, maxIndex()); render(); });

    render();
});

</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.ba-viewport');
  const track = document.querySelector('.ba-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.ba-nav.prev').onclick = () => {
    viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  };
  document.querySelector('.ba-nav.next').onclick = () => {
    viewport.scrollBy({ left: step(), behavior: 'smooth' });
  };

  // click card mở video (hiện chỉ alert, bạn có thể gắn fancybox hoặc modal)
  cards.forEach(c => c.addEventListener('click', () => {
    const url = c.dataset.video;
    if (url) window.open(url, '_blank');
  }));
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.promo-viewport');
  const track = document.querySelector('.promo-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.promo-nav.prev').onclick = () => {
    viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  };
  document.querySelector('.promo-nav.next').onclick = () => {
    viewport.scrollBy({ left: step(), behavior: 'smooth' });
  };
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.salon-viewport');
  const track = document.querySelector('.salon-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.salon-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.salon-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.partner-viewport');
  const track = document.querySelector('.partner-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.partner-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.partner-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.fb-viewport');
  const track = document.querySelector('.fb-track');
  const cards = Array.from(track.children);
  if (!cards.length) return;
  const gap = parseFloat(getComputedStyle(track).gap || 0);
  const step = () => cards[0].getBoundingClientRect().width + gap;

  document.querySelector('.fb-nav.prev').onclick = () => viewport.scrollBy({ left: -step(), behavior: 'smooth' });
  document.querySelector('.fb-nav.next').onclick = () => viewport.scrollBy({ left: step(), behavior: 'smooth' });
});
</script>
