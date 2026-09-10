@extends('layouts.app')
@section('title', 'Media & Files')

@section('content')
<style>
    .md-wrap { max-width: 940px; }

    .md-upload { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        padding: 18px 20px; margin-bottom: 22px; }
    .md-upload-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .md-drop { flex: 1; min-width: 200px; display: flex; align-items: center; gap: 12px;
        border: 1.5px dashed #c4ccd6; border-radius: 8px; padding: 12px 14px; cursor: pointer;
        transition: border-color .15s, background .15s; }
    .md-drop:hover, .md-drop.drag { border-color: var(--accent); background: rgba(79,142,247,.05); }
    .md-drop svg { flex-shrink: 0; color: var(--surface); }
    .md-drop-text { font-size: 13.5px; color: var(--text-sub); }
    .md-drop-text b { color: var(--text); font-weight: 600; }
    input[type=file] { display: none; }

    .md-select { padding: 10px 12px; background: var(--bg); border: 1px solid var(--border);
        border-radius: 7px; color: var(--text); font-family: inherit; font-size: 14px; }

    .md-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
    .md-pill { padding: 7px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600;
        text-decoration: none; border: 1px solid #d5dbe3; color: var(--text); background: #fff; }
    .md-pill.active { background: var(--surface); color: #fff; border-color: var(--surface); }

    .md-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .md-card { background: #fff; border: 1px solid #e2e6ec; border-radius: var(--radius);
        overflow: hidden; display: flex; flex-direction: column; }
    .md-thumb { height: 120px; background: var(--bg); display: flex; align-items: center;
        justify-content: center; overflow: hidden; position: relative; }
    .md-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .md-ext { font-family: 'DM Mono', monospace; font-weight: 500; font-size: 20px;
        color: var(--surface); letter-spacing: .04em; }
    .md-vis { position: absolute; top: 8px; right: 8px; font-size: 9.5px; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase; padding: 2px 7px; border-radius: 20px;
        background: rgba(15,76,129,.85); color: #fff; }

    .md-body { padding: 10px 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .md-name { font-size: 13px; font-weight: 600; word-break: break-word;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .md-meta { font-size: 11px; color: var(--text-sub); font-family: 'DM Mono', monospace; }

    .md-actions { display: flex; gap: 6px; padding: 0 12px 12px; }
    .md-btn { flex: 1; text-align: center; padding: 6px 8px; border-radius: 6px; font-size: 12px;
        font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-family: inherit; }
    .md-btn-dl { background: var(--bg); color: var(--surface); }
    .md-btn-dl:hover { background: #e7eaef; }
    .md-btn-del { background: rgba(248,113,113,.12); color: #c0392b; }
    .md-btn-del:hover { background: rgba(248,113,113,.22); }

    .md-empty { text-align: center; color: var(--text-sub); padding: 56px 20px;
        font-family: 'DM Mono', monospace; font-size: 14px; }
</style>

<div class="page-header">
    <div class="page-title">MEDIA &amp; FILES</div>
    <div class="page-sub">upload & share files — you choose who can see each one</div>
</div>

<div class="md-wrap">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
    @endif

    {{-- ── Upload ──────────────────────────────────────────── --}}
    <form class="md-upload" method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data" id="md-form">
        @csrf
        <div class="md-upload-row">
            <label class="md-drop" id="md-drop">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span class="md-drop-text" id="md-drop-text"><b>Choose a file</b> or drop it here — up to 10 MB</span>
                <input type="file" name="file" id="md-input" required
                       accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip">
            </label>

            <select name="visibility" class="md-select">
                @foreach($visibilities as $value => $label)
                    <option value="{{ $value }}" {{ $value === 'campus' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>

    {{-- ── Filters ─────────────────────────────────────────── --}}
    <div class="md-filters">
        @php $tabs = ['all' => 'All', 'mine' => 'My files', 'campus' => 'My campus', 'public' => 'Public']; @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('pages.media', $key === 'all' ? [] : ['filter' => $key]) }}"
               class="md-pill {{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- ── Grid ────────────────────────────────────────────── --}}
    @if($files->count())
        <div class="md-grid">
            @foreach($files as $file)
                @php
                    $isOwner  = $file->model_type === \App\Models\User::class && (int) $file->model_id === auth()->id();
                    $isImage  = str_starts_with((string) $file->mime_type, 'image/');
                    $vis      = $file->getCustomProperty('visibility');
                    $ext      = strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION) ?: 'FILE');
                @endphp
                <div class="md-card">
                    <div class="md-thumb">
                        <span class="md-vis">{{ $visibilities[$vis] ?? $vis }}</span>
                        @if($isImage)
                            <img src="{{ route('media.show', $file) }}" alt="{{ $file->file_name }}" loading="lazy">
                        @else
                            <span class="md-ext">{{ $ext }}</span>
                        @endif
                    </div>
                    <div class="md-body">
                        <div class="md-name">{{ $file->file_name }}</div>
                        <div class="md-meta">{{ $file->model->name ?? 'Unknown' }} · {{ $file->getCustomProperty('campus') ?? '—' }}</div>
                        <div class="md-meta">{{ $file->human_readable_size }} · {{ $file->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="md-actions">
                        <a href="{{ route('media.download', $file) }}" class="md-btn md-btn-dl">Download</a>
                        @if($isOwner)
                            <form method="POST" action="{{ route('media.destroy', $file) }}"
                                  onsubmit="return confirm('Delete this file?')" style="flex:1;">
                                @csrf @method('DELETE')
                                <button type="submit" class="md-btn md-btn-del" style="width:100%;">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:22px;">{{ $files->links() }}</div>
    @else
        <div class="md-empty">No files here yet — upload something to get started.</div>
    @endif

</div>

<script>
    const input = document.getElementById('md-input');
    const drop  = document.getElementById('md-drop');
    const text  = document.getElementById('md-drop-text');

    input.addEventListener('change', function () {
        if (input.files.length) text.innerHTML = '<b>' + input.files[0].name + '</b> ready to upload';
    });

    ['dragover', 'dragenter'].forEach(function (evt) {
        drop.addEventListener(evt, function (e) { e.preventDefault(); drop.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        drop.addEventListener(evt, function (e) { e.preventDefault(); drop.classList.remove('drag'); });
    });
    drop.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
