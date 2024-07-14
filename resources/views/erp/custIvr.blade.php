<div class="mt-0">    
    <div class="p-2 needs-validation">
        <b>IVR(통화내역)</b>
    </div>
    @include('inc.listSimple')
</div>


<script>


// 오디오 로드 실패시 리로드
function playRecord(url,id)
{
    var video = document.getElementById(id);
    console.log(id+" 오디오 로딩중...");

    $( '#'+id ).empty();
    var source = document.createElement('source');
    source.setAttribute('src', url);
    source.setAttribute('type', 'audio/mp3');
    source.setAttribute('onerror', "reloadAudio('"+id+"')");
    video.appendChild(source);
    video.load();
}
</script>