@php use App\Support\GuestAscent; @endphp
<section class="lg:pt-15 lg:pb-15 pb-10 pt-10" aria-labelledby="student-age-heading">
    <div class="bg-warm lg:py-[120px] py-20 relative z-[1]">
        <div class="container">
            <div class="grid lg:grid-cols-[37%_auto] grid-cols-1 items-center xl:gap-20 gap-10">
                <div>
                    <div class="lg:max-w-[460px]">
                        <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Usia peserta didik</p>
                        <h2 id="student-age-heading" class="font-bold lg:text-[32px] text-2xl lg:leading-[130%] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_student_age_title'] }}</h2>
                    </div>
                    <p class="pt-5 pb-7.5 wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_student_age_text'] }}</p>
                    <a href="{{ route('guest.tentang') }}" class="btn-rounded-full bg-destructive hover:border-destructive hover:text-destructive-foreground wow fadeInUp" data-wow-delay=".5s">Pelajari Lebih Lanjut</a>
                </div>
                <div class="relative flex justify-center flex-wrap sm:flex-nowrap lg:justify-between md:gap-7.5 sm:gap-4 gap-3" aria-hidden="true">
                    <div class="mt-[110px] flex flex-col items-end md:gap-7.5 sm:gap-4 gap-3">
                        <div class="bg-[#0A6375] rounded-[10px] xl:py-20 lg:py-10 py-7 xl:px-[85px] lg:px-10 md:px-6 px-5 lg:max-w-[300px] max-w-[190px] max-h-[300px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[140%] text-center"><span>1-2</span> <span>Tahun</span></h5>
                        </div>
                        <div class="bg-primary rounded-[10px] xl:py-[53px] lg:py-9 py-7 xl:px-10 lg:px-8 md:px-6 px-5 max-w-[190px] max-h-[190px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[130%] text-center"><span>5-6</span> <span>Tahun</span></h5>
                        </div>
                    </div>
                    <div class="flex flex-col md:gap-7.5 sm:gap-4 gap-3">
                        <div class="bg-secondary rounded-[10px] xl:py-[53px] lg:py-9 py-7 xl:px-10 lg:px-8 md:px-6 px-5 max-w-[190px] max-h-[190px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[130%] text-center"><span>3-4</span> <span>Tahun</span></h5>
                        </div>
                        <div class="bg-destructive rounded-[10px] xl:py-[53px] lg:py-9 py-7 xl:px-10 lg:px-8 md:px-6 px-5 max-w-[190px] max-h-[190px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[130%] text-center"><span>TK A/B</span></h5>
                        </div>
                        <div class="bg-green rounded-[10px] xl:py-[53px] lg:py-9 py-7 xl:px-10 lg:px-8 md:px-6 px-5 max-w-[190px] max-h-[190px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[130%] text-center"><span>PAUD</span></h5>
                        </div>
                    </div>
                    <div class="self-center">
                        <div class="bg-primary rounded-[10px] xl:py-[53px] lg:py-9 py-7 xl:px-10 lg:px-8 md:px-6 px-5 max-w-[190px] max-h-[190px]">
                            <h5 class="font-nunito text-cream-foreground lg:text-[32px] text-xl font-bold leading-[130%] text-center"><span>Kelas</span> <span>Campuran</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute 2xl:left-15 left-0 bottom-0 z-[-1] xl:block hidden" aria-hidden="true">
            <img src="{{ GuestAscent::asset('images/shapes/knowledge-lshpe.png') }}" alt="">
        </div>
    </div>
</section>
