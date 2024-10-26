<div id="new-0" class="download-container new" data-id="0">
    <i class="fas fa-times fa-lg remove remove-file"
       style="float: right; margin-left: 10px; margin-top: 3px;"></i>
    <div class="spoiler">
        <div class="spoiler-header" style="cursor: auto;">
            <div style="display: flex; align-items: flex-start;">
                <h2 class="download-title">
                    Размер
                    <input type="text" class="file-block-input size"
                           value="0.0 ГБ" style="text-align: center">
                    Версия
                    <input type="text" class="file-block-input version"
                           value="v0.0" style="text-align: center">
                    @if ($isSponsor)
                    URL
                    <input id="sponsor" type="text" class="file-block-input"
                           value="" style="text-align: center">
                    @endif
                </h2>

                <span class="toggle-icon">▲</span>
            </div>

            <div style="display: flex;">
                @if (!$isSponsor)
                    <div>
                        <input type="file" id="fileInput" data-id="0" accept="{{ $mimeTypeFile }}">
                    </div>
                @endif
            </div>
        </div>
        <div class="spoiler-content" style="text-align: center">
            <textarea id="edit-spoiler-0"></textarea>
            <div class="spoiler-description"></div>
        </div>
    </div>
</div>
