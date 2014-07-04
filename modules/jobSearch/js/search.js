function dataTablesExec() {
    var FILE_LANGUAGE = HTTP_ROOT_DIR+"/modules/jobSearch/js/language/language_"+USER_LANGUAGE+".txt";
//    alert(FILE_LANGUAGE);
//	$j('#container').css('width', '99%');

	var datatable = $j('#sortableTable').dataTable( {
//		'sScrollX': '100%',
                'bLengthChange': true,
		//'bScrollCollapse': true,
		'iDisplayLength': 10,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
		'bProcessing': true,
		'bDeferRender': true,
                "oLanguage": {
                    "sUrl": FILE_LANGUAGE
                },
                'aoColumns': [
                  null,
                  null,
                  { 'sType': "date-eu" },
                  null,
                  null,
                  null
                   ],
                'bPaginate': true,
		'sPaginationType': 'full_numbers'
	}).show();
}

function dataTablesTRelatedExec() {
    var FILE_LANGUAGE = HTTP_ROOT_DIR+"/modules/jobSearch/js/language/language_"+USER_LANGUAGE+".txt";
//    alert(FILE_LANGUAGE);
//	$j('#container').css('width', '99%');

	var datatable = $j('#sortableTable').dataTable( {
//		'sScrollX': '100%',
                'bLengthChange': false,
		'bScrollCollapse': true,
		'iDisplayLength': 20,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
                "oLanguage": {
                    "sUrl": FILE_LANGUAGE
                },
                'aoColumns': [
                  null,
                  null,
                  { 'sType': "numeric" },
                  null,
                  { 'sType': "numeric", "bVisible": false}
                   ],
                 "aaSorting": [[ 4, "desc" ]],  
                'bPaginate': true,
		'sPaginationType': 'full_numbers'
	}).show();
}


function dataTablesExecBIS() {
    var FILE_LANGUAGE = HTTP_ROOT_DIR+"/modules/jobSearch/js/language/language_"+USER_LANGUAGE+".txt";
//	$j('#container').css('width', '99%');

	var datatable = $j('#sortableTable').dataTable( {
//	var datatable = $j('#exercise_table').dataTable( {
//		'sScrollX': '100%',
                'aoColumns': [
                                null,
                                null,
                                { "sType": "date-euro" },                                
                                null,
                                null,
                                null
                            ],
                'bLengthChange': false,
		//'bScrollCollapse': true,
//		'iDisplayLength': 50,
                "bFilter": false,
                "bInfo": false,
                "bSort": true,
                "bAutoWidth": true,
//		'bProcessing': true,
		'bDeferRender': true,
                "aaSorting": [[ 2, "desc" ],[ 3, "desc" ]],
                'bPaginate': false
//		'sPaginationType': 'full_numbers'
	}).show();
}


