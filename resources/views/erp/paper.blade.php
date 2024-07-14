@extends('layouts.master')
@section('content')

<link rel="stylesheet" href="/plugins/summernote/summernote-bs4.min.css">
<?

?>

<!-- Main content -->
<section class="content">
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">

        <div class="card card-lightblue card-outline">

            <form class="form-horizontal" role="form" name="search_form" id="search_form" method="post">

            <div class="card-header">

                <h3 class="card-title">
                <i class="fas fa-search mr-1"></i> 
                [[인쇄물이름]]]
                </h3>

                <div class="card-tools form-inline m-0">

                    <select class="form-control select2 form-control-sm mr-1" id="status" name="status">
                    <option value=''>인쇄물양식</option>
                    
                    </select>
                </div>

            </div>
            </form>

            <div class="card-body">
              <textarea id="summernote" style="display: none;"></textarea>
            </div>

            <div class="card-footer row m-0">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-sm btn-danger" id="paper_btn_del" onclick="">삭제</button>
                    <button type="button" class="btn btn-sm btn-info" onclick="">저장</button>
                </div>
            </div>
            
        </div>
        </div>
    </div>
</div>
</section>



@endsection



@section('lump')
일괄처리할거 입력
@endsection



@section('javascript')

<script src="/plugins/summernote/summernote-bs4.min.js"></script>
<script>

$('#summernote').summernote({
    height: 580,
    lang: 'ko-KR',
});

 
</script>
@endsection