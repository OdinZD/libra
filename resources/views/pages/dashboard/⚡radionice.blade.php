<?php

use App\Models\Workshop;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $naziv = '';
    public string $opis = '';
    public string $dobna_skupina = '';
    public string $ikona = 'calculator';
    public string $boja = 'amber';
    public int $sort_order = 0;

    public static array $iconOptions = [
        'calculator' => 'Kalkulator',
        'book' => 'Knjiga',
        'flask' => 'Epruveta',
        'brush' => 'Kist',
        'people' => 'Ljudi',
        'graduation' => 'Diploma',
    ];

    public static array $colorOptions = [
        'amber' => 'Amber',
        'coral' => 'Coral',
        'purple' => 'Purple',
        'red' => 'Red',
    ];

    #[Computed]
    public function workshops(): \Illuminate\Database\Eloquent\Collection
    {
        return Workshop::orderBy('sort_order')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editWorkshop(int $id): void
    {
        $workshop = Workshop::findOrFail($id);
        $this->editingId = $id;
        $this->naziv = $workshop->naziv;
        $this->opis = $workshop->opis;
        $this->dobna_skupina = $workshop->dobna_skupina;
        $this->ikona = $workshop->ikona;
        $this->boja = $workshop->boja;
        $this->sort_order = $workshop->sort_order;
        $this->showModal = true;
    }

    public function saveWorkshop(): void
    {
        $this->validate([
            'naziv' => 'required|string|max:255',
            'opis' => 'required|string|max:2000',
            'dobna_skupina' => 'required|string|max:255',
            'ikona' => 'required|in:calculator,book,flask,brush,people,graduation',
            'boja' => 'required|in:amber,coral,purple,red',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data = [
            'naziv' => $this->naziv,
            'opis' => $this->opis,
            'dobna_skupina' => $this->dobna_skupina,
            'ikona' => $this->ikona,
            'boja' => $this->boja,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            Workshop::findOrFail($this->editingId)->update($data);
        } else {
            Workshop::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->workshops);
    }

    public function deleteWorkshop(int $id): void
    {
        Workshop::findOrFail($id)->delete();
        unset($this->workshops);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->naziv = '';
        $this->opis = '';
        $this->dobna_skupina = '';
        $this->ikona = 'calculator';
        $this->boja = 'amber';
        $this->sort_order = 0;
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-2">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-libra-warm-text">Radionice</h1>
            <p class="text-sm text-libra-warm-text-secondary">Upravljajte radionicama prikazanim na web stranici</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:from-libra-amber-600 hover:to-libra-coral-600 transition-all"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Dodaj radionicu
        </button>
    </div>

    {{-- Workshop Cards Grid --}}
    @if ($this->workshops->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-libra-amber-100 bg-white py-16 shadow-sm">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-libra-amber-50">
                <svg class="h-7 w-7 text-libra-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
            </div>
            <p class="text-sm font-medium text-libra-warm-text">Nema radionica</p>
            <p class="text-xs text-libra-warm-text-secondary mt-1">Dodajte prvu radionicu klikom na gumb iznad</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->workshops as $workshop)
                @php $colors = $workshop->colorClasses(); @endphp
                <div wire:key="workshop-{{ $workshop->id }}" class="group relative rounded-xl border border-zinc-100 bg-white p-5 shadow-sm hover:border-libra-amber-200 transition-colors">
                    {{-- Edit/Delete buttons --}}
                    <div class="absolute right-3 top-3 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="editWorkshop({{ $workshop->id }})" class="rounded p-1.5 text-libra-warm-text-secondary hover:bg-libra-amber-50 hover:text-libra-amber-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button wire:click="deleteWorkshop({{ $workshop->id }})" wire:confirm="Jeste li sigurni da želite obrisati ovu radionicu?" class="rounded p-1.5 text-libra-warm-text-secondary hover:bg-libra-red-50 hover:text-libra-red-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>

                    {{-- Icon --}}
                    <div class="w-12 h-12 {{ $colors['icon_bg'] }} rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 {{ $colors['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $workshop->iconSvgPath() }}" />
                        </svg>
                    </div>

                    {{-- Badge --}}
                    <span class="inline-block px-2.5 py-0.5 {{ $colors['badge_bg'] }} {{ $colors['badge_text'] }} text-xs font-medium rounded-full mb-3">
                        {{ $workshop->dobna_skupina }}
                    </span>

                    {{-- Title --}}
                    <h3 class="text-base font-bold text-libra-warm-text mb-2">{{ $workshop->naziv }}</h3>

                    {{-- Description --}}
                    <p class="text-sm text-libra-warm-text-secondary leading-relaxed line-clamp-3">{{ $workshop->opis }}</p>

                    {{-- Footer --}}
                    <div class="mt-4 flex items-center justify-between text-xs text-libra-warm-text-secondary">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full {{ $colors['icon_bg'] }}"></span>
                            {{ $workshop->boja }}
                        </div>
                        <span>#{{ $workshop->sort_order }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Workshop Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-libra-warm-text">
                        {{ $editingId ? 'Uredi radionicu' : 'Nova radionica' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="rounded-lg p-1 text-libra-warm-text-secondary hover:bg-zinc-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveWorkshop" class="space-y-4">
                    {{-- Naziv --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-libra-warm-text">Naziv</label>
                        <input
                            wire:model="naziv"
                            type="text"
                            required
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                            placeholder="Naziv radionice"
                        />
                        @error('naziv') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Opis --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-libra-warm-text">Opis</label>
                        <textarea
                            wire:model="opis"
                            rows="3"
                            required
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                            placeholder="Kratki opis radionice..."
                        ></textarea>
                        @error('opis') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Dobna skupina + Sort order --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-libra-warm-text">Dobna skupina</label>
                            <input
                                wire:model="dobna_skupina"
                                type="text"
                                required
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text placeholder-zinc-400 focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                                placeholder="npr. Dob 4-7"
                            />
                            @error('dobna_skupina') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-libra-warm-text">Redoslijed</label>
                            <input
                                wire:model="sort_order"
                                type="number"
                                min="0"
                                required
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                            />
                            @error('sort_order') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Ikona --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-libra-warm-text">Ikona</label>
                        <select
                            wire:model="ikona"
                            class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm text-libra-warm-text focus:border-libra-amber-400 focus:outline-none focus:ring-2 focus:ring-libra-amber-200"
                        >
                            @foreach (self::$iconOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('ikona') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Boja --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-libra-warm-text">Boja</label>
                        <div class="flex gap-3">
                            @foreach (self::$colorOptions as $key => $label)
                                @php
                                    $dotColor = match ($key) {
                                        'amber' => 'bg-libra-amber-400',
                                        'coral' => 'bg-libra-coral-400',
                                        'purple' => 'bg-libra-purple-400',
                                        'red' => 'bg-libra-red-400',
                                    };
                                    $activeBorder = match ($key) {
                                        'amber' => 'border-libra-amber-400 bg-libra-amber-50',
                                        'coral' => 'border-libra-coral-400 bg-libra-coral-50',
                                        'purple' => 'border-libra-purple-400 bg-libra-purple-50',
                                        'red' => 'border-libra-red-400 bg-libra-red-50',
                                    };
                                    $activeText = match ($key) {
                                        'amber' => 'text-libra-amber-600',
                                        'coral' => 'text-libra-coral-600',
                                        'purple' => 'text-libra-purple-600',
                                        'red' => 'text-libra-red-600',
                                    };
                                @endphp
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 px-3 py-2 transition-all
                                    {{ $boja === $key ? $activeBorder : 'border-zinc-200 hover:border-zinc-300' }}">
                                    <input type="radio" wire:model.live="boja" value="{{ $key }}" class="sr-only" />
                                    <span class="h-3 w-3 rounded-full {{ $dotColor }}"></span>
                                    <span class="text-sm font-medium {{ $boja === $key ? $activeText : 'text-libra-warm-text-secondary' }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('boja') <span class="text-xs text-libra-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-libra-warm-text-secondary hover:bg-zinc-50 transition-colors"
                        >
                            Odustani
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:from-libra-amber-600 hover:to-libra-coral-600 transition-all"
                        >
                            Spremi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
