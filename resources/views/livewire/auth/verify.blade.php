<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Verify Your Account')" :description="__('Please submit a document for verification.')" />

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="flex flex-col gap-6">
        <!-- Document Type -->
        <flux:select
            wire:model="document_type"
            :label="__('Document Type')"
            required
        >
            <option value="">{{ __('Select Document Type') }}</option>
            <option value="bvn">{{ __('BVN') }}</option>
            <option value="nin">{{ __('NIN') }}</option>
        </flux:select>
        @error('document_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <!-- Document File -->
        <flux:input
            wire:model="document_file"
            :label="__('Upload Document')"
            type="file"
            required
        />
        @error('document_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Submit for Verification') }}</flux:button>
        </div>
    </form>
</div>