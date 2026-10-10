    <a href="{{ \App\Models\Figure::whatsappUrl($figure->shareText('¡Hola! Quiero pedir esta figura de El Jardín de las Macetas 🌱'), true) }}" target="_blank" rel="noopener" data-confetti="big"
        class="flex min-h-12 w-full active:scale-95 items-center justify-center gap-2 rounded-2xl bg-leaf-500 text-base font-bold text-white shadow transition hover:bg-leaf-600">
        @include('catalog.partials.whatsapp-icon', ['class' => 'h-5 w-5'])
        Pedir por WhatsApp
    </a>
<a href="{{ \App\Models\Figure::whatsappUrl($figure->shareText('¡Mira esta figura de El Jardín de las Macetas! 🌱')) }}" target="_blank" rel="noopener"
    class="flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border-2 border-leaf-500 text-base font-bold text-leaf-700 transition hover:bg-leaf-500 hover:text-white">
    Compartir
</a>
