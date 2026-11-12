@php use App\Support\GuestAscent; @endphp
<section class="lg:pt-15 lg:pb-15 pt-10 pb-10" aria-labelledby="team-heading">
    <div class="container">
        <div class="text-center flex flex-col items-center">
            <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Tim</p>
            <h2 id="team-heading" class="font-bold lg:text-[32px] text-2xl lg:leading-[130%] lg:max-w-[520px] wow fadeInUp" data-wow-delay=".3s">Orang di balik {{ \App\Support\GuestBrand::name() }}</h2>
        </div>
        <div class="lg:pt-15 pt-10">
            <div class="grid lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-7.5">
                @php
                    $members = [
                        ['img' => 'professonal1.png', 'name' => 'Tim Produk', 'role' => 'Pengembangan fitur'],
                        ['img' => 'professonal2.png', 'name' => 'Tim Sukses', 'role' => 'Onboarding sekolah'],
                        ['img' => 'professonal3.png', 'name' => 'Tim Dukungan', 'role' => 'Bantuan teknis'],
                    ];
                @endphp
                @foreach($members as $member)
                <article class="bg-background shadow-3xl border-2 border-transparent hover:border-green transition-all duration-500 flex justify-center lg:p-10 p-7 rounded-tl-[50px] rounded-br-[50px] rounded-tr-[10px] rounded-bl-[10px] max-w-[410px] mx-auto group/team">
                    <div>
                        <img src="{{ GuestAscent::asset('images/team/'.$member['img']) }}" alt="{{ $member['name'] }}" class="rounded-tl-[50px] rounded-br-[50px] rounded-tr-[10px] rounded-bl-[10px] group-hover/team:rounded-tr-[50px] group-hover/team:rounded-bl-[50px] group-hover/team:rounded-tl-[10px] group-hover/team:rounded-br-[10px] transition-all duration-500 w-full" loading="lazy">
                        <div class="pt-7.5 text-center sm:text-left">
                            <h3 class="text-2xl font-medium leading-[141%]">{{ $member['name'] }}</h3>
                            <p class="pt-1 text-muted-foreground">{{ $member['role'] }}</p>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
