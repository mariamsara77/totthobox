  <flux:icon name="folder-open" class="w-12 h-12 text-zinc-400" variant="solid" />

  <flux:heading size="xl">
      {{ $title ?? 'কোনো তথ্য পাওয়া যায়নি' }}
  </flux:heading>

  <flux:text class="">
      {{ $message ?? 'এখানে দেখানোর মতো কোনো ডেটা এই মুহূর্তে নেই।' }}
  </flux:text>

  <flux:button href="{{ url()->previous() }}" icon="chevron-left" variant="subtle">
      পূর্বের পেজে ফিরে যান
  </flux:button>
