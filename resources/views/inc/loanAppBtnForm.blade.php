{{-- <h6 class="card-title"><i class="fas fa-plus-square m-2"></i></h6> --}}
    <div class="row col-md-12 mb-2">
        <!-- <button type="button" class="btn btn-outline-info btn-block btn-xs w-10 m-0 mr-1" onclick="addIntrCust('{{ $loan_app->no }}');"><i class="fa fa-plus-square mr-1"></i>소개고객등록</button> -->
        <button type="button" class="btn btn-outline-danger btn-block btn-xs w-15 m-0 mr-1" onclick="getPopUp('/ups/amlrskform/{{ $loan_app->no }}','aml','');"><i class="fa fa-exclamation-triangle mr-1"></i>위험평가모델산정표</button>
    </div>