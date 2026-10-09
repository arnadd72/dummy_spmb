{{-- Render satu isian formulir dinamis dari konfigurasi admin (FormField).
    Parameter: $field (FormField), $data (array nilai lama/session) --}}
@php
    $kunci = $field->kunci;
    $nilai = old($kunci, $data[$kunci] ?? $field->nilai_default ?? '');
    $adaError = $errors->has($kunci);
    $wajib = (bool) $field->wajib;
    $inputId = 'ff-' . $kunci;
@endphp
<div class="form-group">
    <label class="form-label" for="{{ $inputId }}">
        {{ $field->label }}
        @if ($wajib)<span class="required">*</span>@endif
    </label>

    @if ($field->tipe === 'select')
        <select name="{{ $kunci }}" id="{{ $inputId }}"
                class="form-control {{ $adaError ? 'error' : '' }}"
                @if ($wajib) required aria-required="true" @endif>
            <option value="">— Pilih —</option>
            @foreach ($field->opsi_list as $opsi)
                <option value="{{ $opsi }}" {{ (string) $nilai === (string) $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
            @endforeach
        </select>
    @elseif ($field->tipe === 'radio')
        <div style="display:grid; gap:.5rem; margin-top:.25rem;" role="radiogroup" aria-label="{{ $field->label }}">
            @foreach ($field->opsi_list as $opsi)
                <label style="display:flex; align-items:flex-start; gap:.6rem; padding:.6rem .8rem; border:1px solid var(--color-border); border-radius:var(--radius-md); cursor:pointer; font-size:.9rem;">
                    <input type="radio" name="{{ $kunci }}" value="{{ $opsi }}"
                           {{ (string) $nilai === (string) $opsi ? 'checked' : '' }}
                           @if ($wajib) required @endif
                           style="margin-top:.2rem; accent-color:var(--color-primary);">
                    <span>{{ $opsi }}</span>
                </label>
            @endforeach
        </div>
    @elseif ($field->tipe === 'textarea')
        <textarea name="{{ $kunci }}" id="{{ $inputId }}" rows="3"
                  class="form-control {{ $adaError ? 'error' : '' }}"
                  placeholder="{{ $field->placeholder ?? '' }}"
                  @if ($wajib) required aria-required="true" @endif>{{ $nilai }}</textarea>
    @elseif ($field->tipe === 'checkbox')
        <label style="display:flex; align-items:flex-start; gap:.6rem; padding:.7rem .9rem; border:1px solid var(--color-border); border-radius:var(--radius-md); cursor:pointer; font-size:.9rem; background:var(--color-background);">
            <input type="checkbox" name="{{ $kunci }}" id="{{ $inputId }}" value="1"
                   {{ old($kunci, $data[$kunci] ?? $field->nilai_default ?? false) ? 'checked' : '' }}
                   @if ($wajib) required aria-required="true" @endif
                   style="margin-top:.2rem; width:1.15rem; height:1.15rem; accent-color:var(--color-primary);">
            <span>{{ $field->placeholder ?: 'Ya' }}</span>
        </label>
    @elseif ($field->tipe === 'file')
        <input type="file" name="{{ $kunci }}" id="{{ $inputId }}"
               class="form-control {{ $adaError ? 'error' : '' }}"
               @if ($wajib) required aria-required="true" @endif>
    @else
        <input type="{{ $field->tipe }}" name="{{ $kunci }}" id="{{ $inputId }}"
               class="form-control {{ $adaError ? 'error' : '' }}"
               value="{{ $nilai }}"
               placeholder="{{ $field->placeholder ?? '' }}"
               @if ($wajib) required aria-required="true" @endif>
    @endif

    @if ($field->teks_bantuan)
        <div class="form-hint">{{ $field->teks_bantuan }}</div>
    @endif
    @error($kunci)<div class="form-error">{{ $message }}</div>@enderror
</div>
