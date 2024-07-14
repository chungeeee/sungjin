@extends('layouts.masterPop')
@section('content')
    <form name="" method="" action="">
		@csrf
		<div class="col-md-12 row p-2 m-0" style="width: 80%">
            <h6 class="card-title"><i class="fas fa-list-alt m-2"></i>KB 시세</h6>
            <table class="table table-xs table-bordered text-xs vertical m-0 p-0">
                <colgroup>
                    <col width="50%"/>
                    <col width="50%"/>
                </colgroup> 
                <tbody>
                <tr>
                    <th style="background-color:#f4f6f9; text-align:center;" class="tbg numTd del_tag">시세 갱신일</th>
                    <td align="left">
						<div class="input-group date datetimepicker col-md-8 p-0" id="sise_update_date_div" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker " name="sise_update_date" id="sise_update_date" value="" dateonly="true" required maxlength="10" placeholder="yyyy-mm-dd">
                            <div class="input-group-append" data-target="#sise_update_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div> 
                    </td>
                </tr>
                <tr>
                    <th style="background-color:#f4f6f9; text-align:center;" class="tbg numTd del_tag">기준가(원)</th>
                    <td align="center">
                        <div class="input-group date datetimepicker col-md-12 p-0" id="standard_avg_price" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm col-md-8 comma ml-2" name="gijun_value" id="gijun_value" onkeyup="onlyNumber(this);" maxlength="14">
                        </div> 
                    </td>
                </tr>
            </tbody>
            </table>
			<div class="col-md-12 row m-0" style="text-align:right;">
				<div class="col-md-12">
					<button type="button" class="btn btn-sm btn-info" onclick="setOpenerVal();">적용</button>
				</div>
			</div>
        </div>
	</form>

	<script>
		function setOpenerVal()
		{
			let siseUpdateDate = document.getElementById("sise_update_date").value;
			let gijunValue = document.getElementById("gijun_value").value;
			gijunValue = gijunValue.replace(/,/gi, "");

			if (!confirm("적용하시겠습니까?")) {
				return false;
			} else {
				if (!siseUpdateDate) {
					alert("시세 갱신일을 입력해주세요.");
					return;
				}
				if (!gijunValue || gijunValue == 0) {
					alert("기준가를 입력해주세요.");
					return;
				}
			}

			let dateRegExp = /^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/;
			if (!dateRegExp.test(siseUpdateDate)) {
				alert("날짜 형식에 맞게 입력바랍니다.");
				return;
			}

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				url:"/erp/rekbupdate/{{ $damboNo }}",
				type:"POST",
				data:
				{
					siseUpdateDate: siseUpdateDate,
					gijunValue: gijunValue
				},
				success:function(rs)
				{	
					// console.log(rs.success);
					alert(rs.msg);
					if (rs.success == "Y") {
						window.close();
					}
				},
				error:function(request,status,error)
				{
					console.log("fail");
					console.log(request);
					console.log(status);
					console.log(error);
				}
			});
		}
	</script>
@endsection