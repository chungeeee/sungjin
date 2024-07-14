
/*
	data : ajax 에 보낼 데이터 (Array형태)
	erp_ups : erp / ups 구분 (소문자)
	mode : A : 일괄출력(多건 출력)  // B : 한 건 출력
	directPrint : 바로 출력해야 함.
*/

var ubi_lnos = null;
var ubi_erp_ups = null;
var ubi_post_cd = null;
var ubi_addr = null;

var pJrfDir = null;

// var printFlag = false;

function ubiPrint(data, erp_ups, mode=null, directPrint=null)
{
	if( !data || !erp_ups )
	{
		return false;
	}

	erp_ups = erp_ups.toLowerCase();

    //  -- UBI  뷰어 셋팅
    var app = '';
	var ubiHost = '';

	var appUrl = "http:" + '//' + self.location.host + ":8071" + (app==''?'':('/' + app));

    /* Viewer Object */
    var wsViewer = null;
    var wsViewerWidth = 900;
    var wsViewerHeight = screen.height - 200;
    var wsViewerLeft = (screen.width - wsViewerWidth)/2;
    var wsViewerTop = (screen.height - wsViewerHeight)/2;

    /* Viewer Param */
    var pServerUrl = appUrl + '/UbiServer';
    var pRootUrl = appUrl;
    var pFileUrl = appUrl + '/ubi4';
    var pScale = '-9999';
    var pToolbar = 'true';
    var pProgress = 'true';

	//var invisibletoolbar = "EXPORT_PDF,EXPORT,EXPORT_EXCEL,EXPORT_DOC,EXPORT_PPT,EXPORT_HWP";
	var invisibletoolbar = "EXPORT_DOC,EXPORT_PPT,EXPORT_HWP";

    var pArg = '';
	
	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	var pcd = "";
	for(var i = 0; i < data.length; i++)
	{
		if( data[i].name == "lump_post_cd" )
		{
			pcd = data[i].value;
		}
	}

	$("#LUMPFORM_BTN_PRINT").html(loadingStringtxt);
	$("#LUMPFORM_BTN_PRINT").prop("disabled",true);

	//	일괄출력(多건 출력)
	if( mode == "LUMP" )
	{
		if( directPrint.includes(pcd) == true )
		{
			$.ajax({
				url  : "/"+erp_ups+"/lumpprint",
				type : "post",
				data : data,
				success : function(result) {

					console.log(result);

					if( result.msg )
					{
						$("#LUMPFORM_BTN_PRINT").html('일괄인쇄');
						alert(result.msg);
						return false;
					}
	
					
					
					InitWebSocket(ShowReport);
					
					function ShowReport(ws) {
						$.each(result.UBI, function(idx, item){
							
							pJrf = item.jrfPathFull;
							pDatasource = "";
							$.each(item.dataSet, function(key, v){
		
								pDatasource += key+'#\"';
								pDatasource += v;
								pDatasource += "\"#";
							});
							
							wsViewer = null;
							wsViewer = new UbiWSViewer(ws);
							wsViewer.SetVariable("isLocalData", "true");
							wsViewer.ubiserverurl = pServerUrl;
							wsViewer.servletrooturl = pRootUrl;
							wsViewer.fileurl = pFileUrl;
							wsViewer.datasource = pDatasource;
							wsViewer.jrffiledir = pJrfDir;
							wsViewer.invisibletoolbar = invisibletoolbar;
	
							wsViewer.jrffilename = pJrf;
							wsViewer.arg = pArg;
	
							wsViewer.setResize('hide');
							wsViewer.retrieve();
	
							wsViewer.print();
						});
					}
					
					ubi_lnos = result.lnos;
					ubi_erp_ups = erp_ups;
					ubi_post_cd = result.UBI[0].post_cd;
					ubi_addr = result['addr'];

					$("#LUMPFORM_BTN_PRINT").html('일괄인쇄');
					$("#LUMPFORM_BTN_PRINT").prop("disabled",false);
				},
				error : function(xhr) {
					alert("통신오류입니다. 관리자에게 문의해주세요.");
				}
			});
		}
		else
		{
			//  (^n)이 없는 양식지의 경우엔 전체 미리보기 형태로 띄운다.
			$.ajax({
				url  : "/"+erp_ups+"/lumpprint",
				type : "post",
				data : data,
				// dataType: 'json',
				success : function(result) {

					if( result.msg )
					{
						$("#LUMPFORM_BTN_PRINT").html('일괄인쇄');
						alert(result.msg);
						return false;
					}
	

					InitWebSocket(ShowReport);
	
					function ShowReport(ws) {
	
						wsViewer = null;
						var pDatasource = "";
						pJrf = result.UBI[0].jrfPathFull;

						$.each(result.UBI[0].dataSet, function(key, item){
	
							pDatasource += key+'#\"';
							pDatasource += item;
							pDatasource += "\"#";
						});

						wsViewer = new UbiWSViewer(ws);
						wsViewer.SetVariable("isLocalData", "true");
						wsViewer.ubiserverurl = pServerUrl;
						wsViewer.servletrooturl = pRootUrl;
						wsViewer.fileurl = pFileUrl;
						wsViewer.datasource = pDatasource;
						wsViewer.jrffiledir = pJrfDir;
						wsViewer.invisibletoolbar = invisibletoolbar;
	
						wsViewer.jrffilename = pJrf;
						wsViewer.arg = pArg;

						wsViewer.retrieve();
					}

					ubi_lnos = result.lnos;
					ubi_erp_ups = erp_ups;
					ubi_post_cd = result.UBI[0].post_cd;
					ubi_addr = result['addr'];

					$("#LUMPFORM_BTN_PRINT").html('일괄인쇄');
					$("#LUMPFORM_BTN_PRINT").prop("disabled",false);
				},
				error : function(xhr) {
					alert("통신오류입니다. 관리자에게 문의해주세요.");
				}
			});
		}
	}
	else
	{
		$.ajax({
			url  : "/"+erp_ups+"/printaction",
			type : "post",
			data : data,
			success : function(result) {

				if( result.msg )
				{
					alert(result.msg);
					return false;
				}

				InitWebSocket(ShowReport);

				function ShowReport(ws) {

					wsViewer = null;
					pJrf = result.UBI[0].jrfPathFull;
					pDatasource = "";
					$.each(result.UBI[0].dataSet, function(key, v){

						pDatasource += key+'#\"';
						pDatasource += v;
						pDatasource += "\"#";
					});


					wsViewer = new UbiWSViewer(ws);
					wsViewer.SetVariable("isLocalData", "true");
					wsViewer.ubiserverurl = pServerUrl;
					wsViewer.servletrooturl = pRootUrl;
					wsViewer.fileurl = pFileUrl;
					wsViewer.datasource = pDatasource;
					wsViewer.jrffiledir = pJrfDir;
					wsViewer.invisibletoolbar = invisibletoolbar;

					wsViewer.jrffilename = pJrf;
					wsViewer.arg = pArg;

					wsViewer.retrieve();
				}

				ubi_lnos = result.lnos;
				ubi_erp_ups = erp_ups;
				ubi_post_cd = result.UBI[0].post_cd;
				ubi_addr = result['addr'];
			},
			error : function(xhr) {
				alert("통신오류입니다. 관리자에게 문의해주세요.");
			}
		});

	}
}
	
function after()
{
}

function RetrieveEnd() {

	// printFlag = false;
}

function PrintEnd(status) {

	if( ubi_lnos != null )
	{
		afterPrint(ubi_lnos, ubi_erp_ups, ubi_post_cd, ubi_addr);
	}
}

function ExportEnd(filePath) {

	if( ubi_lnos != null )
	{
		afterPrint(ubi_lnos, ubi_erp_ups, ubi_post_cd, ubi_addr);
	}
}

function Ubi_Version() {

	wsViewer.aboutBox();
}

/*
	lnos : loan_info_no 또는 loan_app_no 배열
	erp_ups : erp / ups 구분
	post_cd : 양식지 구분
*/
function afterPrint(lnos, erp_ups, post_cd, addr=null)
{
    $.ajax({
        url  : "/"+erp_ups+"/lumpafterprint",
        type : "post",
        data : {
            "lno" : lnos,
            "post_cd" : post_cd,
			"addr" : addr
        },
        success : function(result) {

			console.log("확인");
        },
        error : function(xhr) {
            // alert("통신오류입니다. 관리자에게 문의해주세요.");
			console.log(xhr.responseText);
        }
    });

	ubi_lnos = null;
	ubi_erp_ups = null;
	ubi_post_cd = null;
	ubi_addr = null;
}

