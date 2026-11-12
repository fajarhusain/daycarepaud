@php use App\Support\GuestFeatures; @endphp
<section class="lg:pb-15 pb-10 bg-warm" aria-labelledby="onboarding-heading">
    <div class="container">
        <div class="text-center max-w-xl mx-auto">
            <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Cara Mulai</p>
            <h2 id="onboarding-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] mt-2 wow fadeInUp" data-wow-delay=".2s">Tiga Langkah Siap Operasional</h2>
        </div>
        <ol class="grid md:grid-cols-3 grid-cols-1 gap-7.5 lg:pt-12 pt-8 list-none m-0 p-0">
            @foreach(GuestFeatures::onboardingSteps() as $i => $step)
            <li class="rounded-[10px] bg-background border-2 border-[#F2F2F2] lg:p-8 p-6 wow fadeInUp" data-wow-delay="{{ number_format(0.3 + ($i * 0.1), 1) }}s">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-cream-foreground font-bold text-lg">{{ $step['step'] }}</span>
                <h3 class="font-semibold lg:text-xl text-lg mt-5">{{ $step['title'] }}</h3>
                <p class="mt-3 text-sm text-muted-foreground leading-relaxed">{{ $step['desc'] }}</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>
