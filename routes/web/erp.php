<?php	
// 채권관리 - 계약명세	    
Route::get ('/erp/loan',                     'Erp\LoanController@loan')				                ->name('계약명세  메인');
Route::post('/erp/loanlist',                 'Erp\LoanController@loanList')			                ->name('계약명세  리스트');
Route::post('/erp/loanlump',                 'Erp\LoanController@loanLump')			                ->name('계약명세  일괄처리');
Route::post('/erp/loanexcel',                'Erp\LoanController@loanExcel')			            ->name('계약명세  엑셀다운로드');

// DM출력	
Route::get ('/erp/paper',                    'Erp\PaperController@paper')	                        ->name('DM출력');
	
//  조건변경	
Route::get ('/erp/condition',                'Erp\ConditionController@condition')		            ->name('조건변경 메인');
Route::post('/erp/conditionlist',            'Erp\ConditionController@conditionList')		        ->name('조건변경 리스트');
Route::get ('/erp/conditionpop',             'Erp\ConditionController@conditionPop')		        ->name('조건변경 팝업창');
Route::post('/erp/conditionpopaction',       'Erp\ConditionController@conditionPopAction')	        ->name('조건변경 저장');
Route::post('/erp/conditionplanPreview',     'Erp\ConditionController@conditionPlanPreview')	    ->name('조건변경 미리보기');
Route::post('/erp/conditionexcel',           'Erp\ConditionController@conditionExcel')	            ->name('조건변경 엑셀다운로드');

// 고객정보창   
Route::get('/erp/custpop',                   'Erp\CustomerController@custPop')			            ->name('고객정보창 메인');
Route::post('/erp/custinfo',                 'Erp\CustomerController@custInfo')	                    ->name('고객정보');
Route::post('/erp/custinfoaction',           'Erp\CustomerController@custInfoAction')		        ->name('고객정보 저장');
Route::get('/erp/custpopnew',                'Erp\CustomerController@custPopNew')			        ->name('고객정보 메뉴 새창');

// 입출금관리 - 고객관리
Route::get ('/erp/customer',                 'Erp\CustomerController@customer')                     ->name('고객관리 메인');
Route::post('/erp/customerlist',             'Erp\CustomerController@customerList')                 ->name('고객관리 리스트');
Route::get ('/erp/customerpop',              'Erp\CustomerController@customerPop')                  ->name('고객관리 팝업창');
Route::post('/erp/customerinfo',             'Erp\CustomerController@customerInfo')                 ->name('고객관리 고객정보');
Route::post('/erp/customerchange',           'Erp\CustomerController@customerChange')		        ->name('고객관리 변경내역');
Route::post('/erp/customerinfoaction',       'Erp\CustomerController@customerInfoAction')           ->name('고객관리 고객정보 저장');
Route::post('/erp/customerexcel',            'Erp\CustomerController@customerExcel')                ->name('고객관리 엑셀다운로드');
Route::post('/erp/corporatesearch',          'Erp\CustomerController@corporateSearch')              ->name('고객관리 계좌찾기');
Route::view('/erp/custinputpop',             'erp.custInputPop')                                    ->name('고객관리 고객입력창');

// 고객정보창-이전결재정보
Route::get ('/erp/custloanappinfo',          'Erp\CustomerController@custLoanappInfos')             ->name('고객결재 정보 (모든 loan_app결재정보)');

Route::post('/erp/custalarm',                'Erp\CustomerController@custAlarm')                    ->name('고객정보 오른쪽 알람');

Route::get('/erp/custsearch',                'Erp\CustomerController@custSearch')                   ->name('고객검색');

Route::post('/erp/customerdelete',           'Erp\CustomerController@customerDelete')	            ->name('차입자정보 일괄삭제');

//  고객정보창-메모
Route::get ('/erp/custmemo',                 'Erp\CustomerMemoController@custMemo')                 ->name('고객정보창 메모메인');
Route::post('/erp/custmemolist',             'Erp\CustomerMemoController@custMemoList')             ->name('고객정보창 메모리스트');
Route::post('/erp/custmemoinput',            'Erp\CustomerMemoController@custMemoInput')            ->name('고객정보창 메모입력창');
Route::post('/erp/custmemoaction',           'Erp\CustomerMemoController@custMemoAction')           ->name('고객정보창 메모저장');
Route::post('/erp/custimportmemoaction',     'Erp\CustomerMemoController@custimportmemoaction')     ->name('고객정보창 중요메모 저장');
Route::get ('/erp/comemo',                   'Erp\CustomerMemoController@coMemo')                   ->name('부서별주요메모 메인');
Route::post('/erp/comemolist',               'Erp\CustomerMemoController@coMemoList')               ->name('부서별주요메모리스트');
Route::post('/erp/comemoaction',             'Erp\CustomerMemoController@coMemoAction')             ->name('부서별주요메모 저장');

//  고객정보창-SMS
Route::get ('/erp/custsms',                  'Erp\CustomerSmsController@custSms')                   ->name('고객정보창 SMS 메인');
Route::post('/erp/custsmslist',              'Erp\CustomerSmsController@custSmsList')               ->name('고객정보창 SMS 리스트');
Route::post('/erp/custsmsdiv',               'Erp\CustomerSmsController@custSmsDiv')                ->name('고객정보창 SMS 구분값별 문구출력');
Route::post('/erp/custsmspreview',           'Erp\CustomerSmsController@custSmsPreview')            ->name('고객정보창 SMS 미리보기');
Route::post('/erp/custsmsaction',            'Erp\CustomerSmsController@custSmsAction')             ->name('고객정보창 SMS 전송');

//  우편물명세
Route::get ('/erp/post',                     'Erp\CustomerPostController@post')                     ->name('우편물명세 메인');
Route::post('/erp/postlist',                 'Erp\CustomerPostController@postList')                 ->name('우편물명세 리스트');
Route::post('/erp/postexcel',                'Erp\CustomerPostController@postExcel')                ->name('우편물명세 엑셀');

//  고객정보창 - 인쇄
Route::any ('/erp/paperprint',               'Erp\PrintController@openPrint')                       ->name('고객정보창 양식인쇄');
Route::post('/erp/printaction',              'Erp\PrintController@printAction')                     ->name('고객정보창 양식인쇄 action');
Route::post('/erp/printafter',               'Erp\PrintController@printAfter')                      ->name('고객정보창 양식인쇄 after');

//  일괄인쇄
Route::post('/erp/lumpprint',                'Erp\PrintController@lumpPrint')                       ->name('일괄인쇄');
Route::get ('/erp/lumpopen',                 'Erp\PrintController@lumpOpen')                        ->name('일괄인쇄 open');
Route::post('/erp/lumpafterprint',           'Erp\PrintController@lumpAfterPrint')                  ->name('일괄인쇄 after');    

// 고객정보창 - 신복계좌번호
Route::any ('/erp/ccrsaccount',              'Erp\LoanController@loanCcrsAccount')                  ->name('고객정보창 신복계좌번호');
Route::post('/erp/ccrsaccountaction',        'Erp\LoanController@loanCcrsAccountAction')            ->name('고객정보창 신복계좌번호 ACTION');

Route::post('/erp/loanmain',                 'Erp\LoanController@loanMain')                         ->name('고객정보 하단 계약정보 메인');
Route::post('/erp/loaninfo',                 'Erp\LoanController@loanInfo')                         ->name('계약상세정보');
Route::post('/erp/loaninfointerestcal',      'Erp\LoanController@loanInfoInterestCal')              ->name('계약상세정보 - 이자계산');
Route::post('/erp/loantrade',                'Erp\LoanController@loanTrade')                        ->name('거래원장');
Route::any ('/erp/loaninterest',             'Erp\LoanController@loanInterest')                     ->name('예상이자');
Route::post('/erp/loanplan',                 'Erp\LoanController@loanPlan')                         ->name('상환스케줄');
Route::post('/erp/loansettleplan',           'Erp\LoanController@loanSettlePlan')                   ->name('화해상환스케줄');
Route::any ('/erp/loanreturnpreview',        'Erp\LoanController@loanReturnPreview')                ->name('입금미리보기');
Route::post('/erp/loanlog',                  'Erp\LoanController@loanLog')                          ->name('변경내역');


Route::post('/erp/loanguarantor',            'Erp\LoanController@loanGuarantor')                    ->name('보증인');
Route::post('/erp/loanguarantorinfo',        'Erp\LoanController@loanGuarantorInfo')                ->name('보증인상세정보');
Route::post('/erp/loanguarantoraction',      'Erp\LoanController@loanGuarantorAction')              ->name('보증인 ACTION');
Route::post('/erp/loanguarantorremoveaction','Erp\LoanController@loanGuarantorRemoveAction')        ->name('보증인 삭제');
Route::post('/erp/loandoc',                  'Erp\LoanController@loanDoc')                          ->name('징구서류');
Route::post('/erp/loandocaction',            'Erp\LoanController@loanDocAction')                    ->name('징구서류액션');
Route::post('/erp/loancms',                  'Erp\LoanController@loanCms')                          ->name('CMS');
Route::post('/erp/loanchanges',              'Erp\LoanController@loanChanges')                      ->name('계약변경내역');
Route::post('/erp/loantotal',                'Erp\LoanController@loanTotal')                        ->name('계좌통합정보');
Route::post('/erp/loanviraccount',           'Erp\LoanController@loanVirAccount')                   ->name('가상계좌정보');
Route::post('/erp/loanviraccountaction',     'Erp\LoanController@loanVirAccountAction')             ->name('가상계좌정보 ACTION');
Route::get ('/erp/loanlostdate',             'Erp\LoanController@loanLostDate')                     ->name('소멸시효변경로그팝업');

// 부동산담보
Route::post('/erp/custrealestate',           'Erp\MortgageController@custRealEstate')               ->name('부동산담보정보');
Route::post('/erp/custrealestatelist',       'Erp\MortgageController@custRealEstateList')           ->name('부동산담보정보 list');
Route::post('/erp/custrealestateaction',     'Erp\MortgageController@custRealEstateAction')         ->name('부동산담보 action');
// 차
Route::post('/erp/custcar',                  'Erp\MortgageController@custCar')                      ->name('차량담보정보');
Route::post('/erp/custcarlist',              'Erp\MortgageController@carList')                      ->name('차량담보리스트');
Route::post('/erp/caraction',                'Erp\MortgageController@carAction')                    ->name('차량담보액션');
// 고객정보창-발급서
Route::post('/erp/custissue',                'Erp\IssueController@custIssueForm')                   ->name('고객정보창 발급서류');
Route::post('/erp/custissueaction',          'Erp\IssueController@custIssueAction')                 ->name('고객정보창 발급서류 저장');
// 발급서류리스트
Route::get ('/erp/custissue',                'Erp\IssueController@issue')                           ->name('발급서류 메인');
Route::post('/erp/custissuelist',            'Erp\IssueController@issueList')                       ->name('발급서류 리스트');
Route::post('/erp/custissueexcel',           'Erp\IssueController@issueExcel')                      ->name('발급서류 엑셀');
// 법착내역
Route::post('/erp/custlaw',                  'Erp\LawController@custLaw')                           ->name('고객정보창 법착');
Route::post('/erp/custlawexcel',             'Erp\LawController@custLawExcel')                      ->name('고객정보창 법착 엑셀');
Route::post('/erp/custlawinfo',              'Erp\LawController@custLawInfo')                       ->name('고객정보창 법착 상세정보');
Route::post('/erp/custlawaction',            'Erp\LawController@custLawAction')                     ->name('고객정보창 법착 ACTION');
Route::get('/erp/lawpostage',                'Erp\LawController@lawpostage')                        ->name('고객정보창 법착 송달료 검색 팝업');
Route::post('/erp/lawpostagelist',           'Erp\LawController@lawpostageList')                    ->name('고객정보창 법착 송달료 리스트');
Route::post('/erp/lawpostageexcel',          'Erp\LawController@lawpostageExcel')                   ->name('고객정보창 법착 송달료 리스트 엑셀');
Route::post('/erp/lawpostageform',           'Erp\LawController@lawpostageForm')                    ->name('고객정보창 법착 송달료 Form');
Route::post('/erp/lawpostageaction',         'Erp\LawController@lawpostageAction')                  ->name('고객정보창 법착 송달료 Action');
Route::get ('/erp/lawcancelform',            'Erp\LawController@lawCancelForm')                     ->name('고객정보창 법착 취하/해지요청 form');
Route::post('/erp/lawcancelaction',          'Erp\LawController@lawCancelAction')                   ->name('고객정보창 법착 취하/해지요청 action');
Route::post('/erp/adddebtor',                'Erp\LawController@addDebtor')                         ->name('제3채무자 row 추가');
Route::post('/erp/custdebtoraction',         'Erp\LawController@custDebtorAction')                  ->name('제3채무자 추가 ACTION');
Route::post('/erp/deletedebtor',             'Erp\LawController@deleteDebtor')                      ->name('제3채무자 삭제 ACTION');
Route::post('/erp/custdocumentaction',       'Erp\LawController@custDocumentAction')                ->name('법착 양식 ACTION');
Route::get('/erp/printview',                 'Erp\LawController@printLawForm')                      ->name('법착양식인쇄');
Route::post('/erp/custeventaction',          'Erp\LawController@custEventAction')                   ->name('법착 사건정보 ACTION');


// 입출금관리 - 계약관리
Route::get ('/erp/loanmng',                  'Erp\LoanController@loanMng')                          ->name('계약관리 메인');
Route::post('/erp/loanmnglist',              'Erp\LoanController@loanMngList')                      ->name('계약관리 리스트');
Route::post('/erp/loanmngexcel',             'Erp\LoanController@loanMngExcel')                     ->name('계약관리 엑셀');
Route::get ('/erp/loanmngform',              'Erp\LoanController@loanMngForm')                      ->name('계약등록 입력폼');
Route::post('/erp/loanmngcustsearch',        'Erp\LoanController@loanMngCustSearch')                ->name('계약등록 차입자찾기');
Route::post('/erp/loanmngusrsearch',         'Erp\LoanController@loanMngUsrSearch')                 ->name('계약등록 투자자찾기');
Route::post('/erp/loanmngaction',            'Erp\LoanController@loanMngAction')                    ->name('계약등록 실행');
Route::post('/erp/loanmnglumpdelete',        'Erp\LoanController@loanMngLumpDelete')                ->name('상품계약 일괄삭제');

// 채권관리 - 사모사채계약
Route::get ('/erp/loanprivately',            'Erp\LoanController@loanPrivately')                    ->name('사모사채계약 메인');
Route::post('/erp/loanprivatelylist',        'Erp\LoanController@loanPrivatelyList')                ->name('사모사채계약 리스트');
Route::post('/erp/loanprivatelyexcel',       'Erp\LoanController@loanPrivatelyExcel')               ->name('사모사채계약 엑셀');

// 입출금관리 - 출금리스트 
Route::get ('/erp/tradeout',                 'Erp\TradeOutController@tradeOut')                     ->name('거래원장(출금) 메인화면');
Route::post('/erp/tradeoutlist',             'Erp\TradeOutController@tradeOutList')                 ->name('거래원장(출금) 리스트');
Route::post('/erp/tradeoutexcel',            'Erp\TradeOutController@tradeOutExcel')                ->name('거래원장(출금) 엑셀');
Route::get ('/erp/tradeoutform',             'Erp\TradeOutController@tradeOutForm')                 ->name('거래원장(출금) 입력폼');
Route::post('/erp/tradeoutaction',           'Erp\TradeOutController@tradeOutAction')               ->name('거래원장(출금) 처리');
Route::post('/erp/tradeoutdelete',           'Erp\TradeOutController@tradeOutDelete')               ->name('거래원장(출금) 일괄삭제');
Route::post('/erp/tradeoutsearch',           'Erp\TradeOutController@searchLoanInfo')               ->name('거래원장(출금) 계약찾기');
Route::post('/erp/tradeoutbankset',          'Erp\TradeOutController@setLoanInfoBank')              ->name('거래원장(출금) 계약찾기(계약의 송금계좌)');
Route::post('/erp/tradeoutbankchk',          'Erp\TradeOutController@chkLoanInfoBank')              ->name('송금계좌의 예금주명 조회');
Route::post('/erp/tradeoutbankinfo',         'Erp\TradeOutController@tradeOutBankInfo')             ->name('송금계좌의 정보조회 (송금오류시)');
Route::post('/erp/tradeoutbankinfoaction',   'Erp\TradeOutController@tradeOutBankInfoAction')       ->name('송금계좌의 정보조회 처리 (송금오류시)');
Route::post('/erp/tradeoutcertchk',          'Erp\TradeOutController@chkLoanInfoCert')              ->name('고액송금 승인처CustData리');

// 입출금관리 - 입금리스트
Route::get ('/erp/tradein',                  'Erp\TradeInController@tradeIn')                       ->name('거래원장(입금) 메인화면');
Route::post('/erp/tradeinlist',              'Erp\TradeInController@tradeInList')                   ->name('거래원장(입금) 리스트');
Route::post('/erp/tradeinexcel',             'Erp\TradeInController@tradeInExcel')                  ->name('거래원장(입금) 엑셀');
Route::get ('/erp/tradeinform',              'Erp\TradeInController@tradeInForm')                   ->name('거래원장(입금) 입력폼');
Route::post('/erp/tradeinaction',            'Erp\TradeInController@tradeInAction')                 ->name('거래원장(입금) 처리');
Route::post('/erp/tradeindeletelump',        'Erp\TradeInController@tradeInDeleteLump')             ->name('거래원장(입금) 일괄삭제요청 결재 ACTION');
Route::post('/erp/tradeindelete',            'Erp\TradeInController@tradeInDelete')                 ->name('거래원장(입금) 일괄삭제');
Route::post('/erp/tradeinsearch',            'Erp\TradeInController@searchLoanInfo')                ->name('거래원장(입금) 계약찾기');
Route::post('/erp/tradeininterest',          'Erp\TradeInController@setLoanInfoInterest')           ->name('거래원장(입금) 계약정보 등 (입금일의 이자 정보)');

// 입출금관리 - 가수거래명세
Route::get ('/erp/tradeover',                'Erp\TradeOverController@tradeOver')                   ->name('가수금명세 메인');
Route::post('/erp/tradeoverlist',            'Erp\TradeOverController@tradeOverList')               ->name('가수금명세 리스트');
Route::post('/erp/tradeoverexcel',           'Erp\TradeOverController@tradeOverExcel')              ->name('가수금명세 엑셀');
Route::post('/erp/tradeoveraction',          'Erp\TradeOverController@tradeOverAction')             ->name('가수금명세 실행');

// 입출금관리 - 결산명세서
Route::get ('/erp/tradeinterest',            'Erp\TradeInterestController@tradeInterest')           ->name('결산명세서 메인');
Route::post('/erp/tradeinterestlist',        'Erp\TradeInterestController@tradeInterestList')       ->name('결산명세서 리스트');
Route::post('/erp/tradeinterestexcel',       'Erp\TradeInterestController@tradeinterestExcel')      ->name('결산명세서 엑셀');

// 입출금관리 - 완제가수금정리
Route::get ('/erp/fullpayover',              'Erp\FullpayOverController@over')                      ->name('완제 정리 메인화면');
Route::post('/erp/fullpayoverlist',          'Erp\FullpayOverController@overList')                  ->name('완제가수금정리 리스트');

// 일괄처리 - 담당자변경
Route::post('/erp/lumpchangemanager',        'Erp\LumpController@changeManager')                    ->name('계약명세 일괄 담당자변경');

// 채권관리 - 징구서류명세
Route::get ('/erp/doc',                      'Erp\DocController@doc')                               ->name('징구서류명세 메인');
Route::post('/erp/doclist',                  'Erp\DocController@docList')                           ->name('징구서류명세 리스트');
Route::post('/erp/docexcel',                 'Erp\DocController@docExcel')                          ->name('징구서류명세 엑셀');
Route::any ('/erp/docinfo',                  'Erp\DocController@docInfo')                           ->name('징구서류 상세정보');
Route::any ('/erp/docinfoaction',            'Erp\DocController@docInfoAction')                     ->name('징구서류 상세정보 저장');

//  채권관리 - DM출력(계약서실)
Route::get ('/erp/postcr',                   'Erp\CustomerPostController@postCr')                   ->name('DM출력(계약서실)');
Route::post('/erp/postcrlist',               'Erp\CustomerPostController@postCrList')               ->name('DM출력(계약서실) 리스트');
Route::post('/erp/postcrexcel',              'Erp\CustomerPostController@postCrExcel')              ->name('DM출력(계약서실) 엑셀');

// 채권관리 - 법착명세
Route::get ('/erp/law',                      'Erp\LawController@law')                               ->name('법착명세 메인');
Route::post('/erp/lawlist',                  'Erp\LawController@lawList')                           ->name('법착명세 리스트');
Route::post('/erp/loan_lawexcel',            'Erp\LawController@loan_lawExcel')                     ->name('법착명세 엑셀다운로드');
Route::post('/erp/lawxml',                   'Erp\LawController@lawXml')                            ->name('법착명세 XML 파일생성');
Route::get ('/erp/lawxmldown',               'Erp\LawController@lawXmlDown')                        ->name('법착명세 XML 다운로드');

// 채권관리 - 상환완료명세
Route::get ('/erp/fullpayment',              'Erp\FullPaymentController@fullPayment')               ->name('상환완료명세 메인');
Route::post('/erp/fullpaymentlist',          'Erp\FullPaymentController@fullPaymentList')           ->name('상환완료명세 리스트');    
Route::post('/erp/fullpaymentexcel',         'Erp\FullPaymentController@fullPaymentExcel')          ->name('상환완료명세 엑셀다운로드');
Route::post('/erp/fullpaymentlumpaction',    'Erp\FullPaymentController@fullPaymentLumpAction')     ->name('상환완료명세 일괄처리');

// 채권관리 - 상각계약명세
Route::get ('/erp/loansanggak',              'Erp\LoanSanggakController@loanSanggak')               ->name('상각계약명세 메인');
Route::post('/erp/loansanggaklist',          'Erp\LoanSanggakController@loanSanggakList')           ->name('상각계약명세 리스트');    
Route::post('/erp/loansanggakexcel',         'Erp\LoanSanggakController@sanggakExcel')              ->name('상각계약명세 엑셀다운로드');
// 채권관리 - 매각계약명세
Route::get ('/erp/loansell',                 'Erp\LoanSellController@loanSell')                     ->name('매각계약명세 메인');
Route::post('/erp/loanselllist',             'Erp\LoanSellController@loanSellList')                 ->name('매각계약명세 리스트');    
Route::post('/erp/loansellexcel',            'Erp\LoanSellController@sellExcel')                    ->name('매각계약명세 엑셀다운로드');

// 채권관리 - 이미지파일명세
Route::get ('/erp/img',                      'Erp\ImgController@img')                               ->name('이미지파일명세 메인');
Route::post('/erp/imglist',                  'Erp\ImgController@imgList')                           ->name('이미지파일명세 리스트');
Route::post('/erp/imgexcel',                 'Erp\ImgController@imgExcel')                          ->name('이미지파일명세 엑셀');

// 채권관리 - 녹취파일명세
Route::get ('/erp/wav',                      'Erp\WavController@wav')                               ->name('녹취파일명세 메인');
Route::post('/erp/wavlist',                  'Erp\WavController@wavList')                           ->name('녹취파일명세 리스트');
Route::post('/erp/wavexcel',                 'Erp\WavController@wavExcel')                          ->name('녹취파일명세 엑셀');

//  방문요청
Route::get ('/erp/visit',                    'Erp\VisitController@visit')                           ->name('방문요청 메인');
Route::post('/erp/visitlist',                'Erp\VisitController@visitList')                       ->name('방문요청 리스트');    
Route::post('/erp/visitexcel',               'Erp\VisitController@visitExcel')                      ->name('방문요청 엑셀');
Route::get ('/erp/visitrequest',             'Erp\VisitController@visitRequestPop')                 ->name('방문요청 입력창');
Route::post('/erp/visitrequestaction',       'Erp\VisitController@visitRequestAction')              ->name('방문요청 저장');
Route::post('/erp/visitcancellump',          'Erp\VisitController@visitCancelLump')                 ->name('방문요청 일괄취소');    
Route::get ('erp/visitLogs',                 'Erp\VisitController@getVisitLog')                     ->name('방문요청 로그');

//전체메모명세
Route::get ('/erp/allmemo',                  'Erp\AllMemoController@allmemo')                       ->name('전체메모명세 메인');
Route::post('/erp/allmemolist',              'Erp\AllMemoController@allmemoList')                   ->name('전체메모명세 리스트');
Route::post('/erp/allmemoexcel',             'Erp\AllMemoController@allmemoExcel')                  ->name('전체메모명세 엑셀다운로드');

//고객정보변경내역
Route::get ('/erp/custinfochange',           'Erp\CustInfoChangeController@custInfoChange')         ->name('고객정보변경내역 메인');
Route::post('/erp/custinfochangelist',       'Erp\CustInfoChangeController@custInfoChangeList')     ->name('고객정보변경내역 리스트');
Route::post('/erp/custinfochangeexcel',      'Erp\CustInfoChangeController@custInfoChangeExcel')    ->name('고객정보변경내역 엑셀다운로드');

//채권정보변경내역
Route::get ('/erp/loaninfochange',           'Erp\LoanInfoChangeController@loanInfoChange')         ->name('채권정보변경내역 메인');
Route::post('/erp/loaninfochangelist',       'Erp\LoanInfoChangeController@loanInfoChangeList')     ->name('채권정보변경내역 리스트');
Route::post('/erp/loaninfochangeexcel',      'Erp\LoanInfoChangeController@loanInfoChangeExcel')    ->name('채권정보변경내역 엑셀다운로드');

//민원관리
Route::get ('/erp/complain',                 'Erp\ComplainController@complain')                     ->name('민원관리 메인');
Route::post('/erp/complainlist',             'Erp\ComplainController@complainList')                 ->name('민원관리 리스트');
Route::get ('/erp/complainform',             'Erp\ComplainController@complainForm')                 ->name('민원관리 입력 form');
Route::post('/erp/searchcomplaininfo',       'Erp\ComplainController@searchComplainInfo')           ->name('민원관리 검색리스트');
Route::post('/erp/getcustinfo',              'Erp\ComplainController@getCustInfo')                  ->name('민원관리 고객정보 가져오기');
Route::post('/erp/complainaction',           'Erp\ComplainController@complainAction')               ->name('민원관리 입력 저장');
Route::post('/erp/complainexcel',            'Erp\ComplainController@complainExcel')                ->name('민원관리 엑셀다운로드');

Route::get('/erp/complainanalysis',          'Erp\ComplainController@complainAnalysis')             ->name('민원관리 현황분석');

Route::get ('/erp/wonjang',                  'Erp\WonjangController@wonjang')                       ->name('원장변경내역 메인');
Route::post('/erp/wonjanglist',              'Erp\WonjangController@wonjangList')                   ->name('원장변경내역 리스트');
Route::post('/erp/wonjangexcel',             'Erp\WonjangController@wonjangExcel')                  ->name('원장변경내역 엑셀다운로드');

// 엑셀다운명세
Route::get ('/erp/excel',                    'Erp\ExcelController@excel')                           ->name('엑셀다운 메인');
Route::post('/erp/excellist',                'Erp\ExcelController@excelList')                       ->name('엑셀다운 리스트');
Route::post('/erp/excelexcel',              'Erp\ExcelController@excelExcel')                     ->name('엑셀파일생성');
Route::post('/erp/exceldown',                'Erp\ExcelController@excelDown')                       ->name('엑셀다운 바로실행');

// 결재업무 - SMS발송
Route::get ('/erp/smscheck',                 'Erp\SmsCheckController@smsCheck')                     ->name('sms발송결재 메인');
Route::post('/erp/smschecklist',             'Erp\SmsCheckController@smsCheckList')                 ->name('sms발송결재 리스트');
Route::get ('/erp/smscheckform/{check_no}',  'Erp\SmsCheckController@smsCheckForm')                 ->name('sms발송결재 입력창');
Route::post('/erp/smscheckaction',           'Erp\SmsCheckController@smsCheckAction')               ->name('sms발송결재 저장');

// 채권관리 - 소멸시효명세
Route::get ('/erp/nulllimitdate',            'Erp\NullLimitDateController@nullLimitDate')           ->name('소멸시효명세 메인');
Route::post ('/erp/nulllimitdatelist',       'Erp\NullLimitDateController@nullLimitDateList')       ->name('소멸시효명세 리스트');
Route::post ('/erp/nulllimitdateexcel',      'Erp\NullLimitDateController@nullLimitDateExcel')      ->name('소멸시효명세 엑셀');

Route::post('/erp/getaddmonth',              'Erp\LoanController@getAddMonth')                      ->name("만기일 계산");

//상단바 - 채권번호 검색
Route::get ('/erp/bondsearch/{searchStr?}',  'Erp\SearchController@bondSearch')                     ->name("채권번호");
Route::post('/erp/bondsearch',               'Erp\SearchController@searchNo')                       ->name("채권번호 검색");
Route::post('/erp/searchNo',                 'Erp\SearchController@searchNo')                       ->name("채권번호 검색 리스트"); 

//상단바 - 투자자 검색
Route::get ('/erp/search/{searchStr?}',      'Erp\SearchController@search')                         ->name("연계검사1");
Route::get ('/erp/searchloan/{searchStr}',   'Erp\SearchController@searchLoan')                     ->name("계약번호바로가기1"); 
Route::post('/erp/searchaction',             'Erp\SearchController@searchAction')                   ->name("연계검사1 저장"); 
Route::post('/erp/searchaction2',            'Erp\SearchController@searchAction2')                  ->name("연계검사1 저장2");

?>