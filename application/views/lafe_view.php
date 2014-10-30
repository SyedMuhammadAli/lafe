<?php
    require 'openid.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to LAFE (Beta)</title>
	
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    <script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
    <script src="http://timeago.yarp.com/jquery.timeago.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css" />
    <link rel="stylesheet" href="http://localhost/lafe/css/style.css" />
    
	<style type="text/css">
    
	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 48px 15px 10px 15px;
	}
	
    label, input, textarea { display:block; }
    input.text { margin-bottom:12px; padding: .4em; }
    fieldset { padding:0; border:0; margin-top:25px; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    
    #report-item {
        position: absolute;
        top: 5px;
        left: 80%;
    }
    
    #login-google-openid {
        position: absolute;
        background-image: url('images/google-login-button.png');
        width: 150px;
        height: 29px;
        background-size:150px 29px;
        background-repeat:no-repeat;
        top: 5px;
        left: 75%;
    }
    
    #search-form {
        position: absolute;
        top: 23%;
        left: 25%;
    }
    
    #results-container {
    	padding-top: 120px;
    	padding-left: 20px;
    	width: 100%;
    	height: 100%;
    }
    
    #results-container div {
        /*float: left;*/
    }
    /*
    #search-result {
        width: 50%;
        float: left;
    }
    
    #recent-lost {
    	width: 50%;
    	float: left;
    }
    */
    .ui-autocomplete-loading {
        background: white url('images/loading.gif') right center no-repeat;
    }
    
    p { margin: 0px; padding: 0px; }
    
    .result-box {
    	width: 50%;
    	padding: 0px;
    	margin-bottom: 20px;
    	font-family: sans-serif;
    	font-size: 14px;
    }
    
    .result-heading {
    	font-family: sans-serif;
    	font-size: 14px;
    	color: #12c;
    }
    
    li {
        margin: 0px;
        padding: 0px;
        float: left;
        display: block;
    }
    
    ul {
        margin: 0px;
        margin-left: 10px;
        padding: 0px;
        display: block;
        float: left;
    }
    
    #header-top {
        position: absolute;
        top: 5px;
    }
    
    #pagination-div{
        padding:10px;
        margin: 10px auto;
        border: 1px solid #fff;
        background-color:#f7f7f7;
    }
	</style>
	
	<script src="http://localhost/lafe/js/jquery.paginate.js" type="text/javascript"></script>

	<script>
	$(function(){
	    var summary = $("#summary");
	    var date = $("#date");
	    var email = $("#email");
	    var lock = $("#lock");
	    
	    //pagination config
	    var pagination_per_page = 5;
	    var pagination_num_records = 50;
		var pagination_start_page = 1;
		
	    //to add search results to div
        function populate_search_results(done){
            done = done[1]; //1st index has data - temp fix
            $("#search-result").html("<h3>Results</h3>");
            if(done.length == 0){
                $("#search-result").append("<p> No match found. </p>");    
            }
            
            var curr_time = new Date().getTime();
            
            for(var i in done){
                $("#search-result").append(
                "<div class='result-box'>" +
                "<p class='result-heading'>" + done[i].email.substring(0, 7) +
                " reported " + jQuery.timeago(new Date(done[i].report_time*1000)) + "</p>" +
                "<p>" + done[i].summary +"</p>" +
                "<p> Score: " + done[i].score +"</p>" +
                "</div>"
                );
            }
        }
	    
	    function generate_pagination_config(){ //per_page, num_records
	        var pagination_config = {
			    per_page    : pagination_per_page,
				count 		: Math.ceil(pagination_num_records/pagination_per_page)+1,
				start 		: pagination_start_page,
				display     : Math.ceil(pagination_num_records/pagination_per_page)+1,
				border					: false,
				text_color  			: '#888',
				background_color    	: '#EEE',	
				text_hover_color  		: 'black',
				background_hover_color	: '#CFCFCF',
				mouse                   : 'press',
				onChange                : function(page_no){
				    $.post("http://localhost/lafe/index.php/lafe/submit_query/",
                    { search_query : $("#search_query").val(), pp : this.per_page, page : page_no },
                    function(done){
                    	pagination_start_page = page_no;
                    	//alert("setting " + pagination_num_records + " to " + done[1].length);
                    	//pagination_num_records = done[1].length;
                        $("#pagination").paginate(generate_pagination_config());
                        populate_search_results(done);
                    }, "json");
		        }
			};
			
			return pagination_config;
	    }
	    
	    $(function(){
			$("#pagination").paginate(generate_pagination_config()); //per_page, num_records			
			$("#pagination-div").hide();
		});
	    
	    $( "#dialog-form" ).dialog({
	        autoOpen: false,
	        height: 300,
	        width: 450,
	        modal: true,
	        buttons: {
	            "Submit" : function(){
	                $.post("http://localhost/lafe/index.php/lafe/submit_item/",
	                       { summary: summary.val(), date: date.val(), email : email.val(), lock : lock.val() })
	                       .done(function(done){ $("#dialog-form").dialog("close"); });
	                
	                summary.val("");
	                date.val("");
	            },
	            Cancel : function(){ $(this).dialog("close"); }
	        },
	        close: function(){
	            summary.val("");
	            date.val("");
	        }
	    });
	    
	    $("#report-item")
	    .button()
        .click(function() {
            $( "#dialog-form" ).dialog( "open" );
        });
        
        $("#search-btn")
	    .button()
        .click(function() {    
            $.post("http://localhost/lafe/index.php/lafe/submit_query/",
                { search_query : $("#search_query").val(), pp : pagination_per_page, page : pagination_start_page },
                function(done){
                    $("#pagination-div").show();
                    pagination_num_records = done[1].length;
                    //alert(pagination_num_records);
                    $("#pagination").paginate(generate_pagination_config());
                    populate_search_results(done);
                }, "json");
        });
        
        $("#search_query").keypress(function(event){
            if(event.which == 13){
                $("#search-btn").button().click();
            } else if(event.which == 32){
                $("#search-btn").button().click();
            }
        });
        
        $("#login-google-openid").button();
        
        $("#search_query").autocomplete({
            source: function(request, response){
                $.post("http://localhost/lafe/index.php/lafe/autocomplete_json", { term : request.term })
                .done( function(done){ response( JSON.parse(done) ); } );
            },
            minLength: 2,
            delay: 500
        });
        
        $("#lost-hlink").click(function(){
            $("#search_query").val("lost");
            $("#search-btn").button().click();
        });
        
        $("#found-hlink").click(function(){
            $("#search_query").val("found");
            $("#search-btn").button().click();
        });
	});
	</script>
</head>
<body>
    <div id="header-top">
        <li>
            <ul id="lost-hlink">
            <a href="#">Lost</a>
            </ul>
            <ul id="found-hlink">
            <a href="#">Found</a>
            </ul>
        <li>
    </div>
    
        <?php
            try {
                $openid = new LightOpenID('localhost');
                if(!$openid->mode) {
                    if(isset($_GET['login'])) {
                        $openid->identity = 'https://www.google.com/accounts/o8/id';
                        $openid->required = array('contact/email');
                        header('Location: ' . $openid->authUrl());
                    }
        ?>
    
    <form action="?login" method="post">
        <button id="login-google-openid"></button>
    </form>
    
    <?php
            } elseif($openid->mode == 'cancel') {
    ?>
    
    <form action="?login" method="post">
        <button id="login-google-openid"></button>
    </form>
    
    <?php
            } else {
                if($openid->validate()){
                    $openid_attr = $openid->getAttributes(); 
                    $_SESSION['email'] = $openid_attr['contact/email'];
                    $_SESSION['lock'] = hash('sha256', $openid_attr['contact/email'].session_id());
                }
                echo "<button id='report-item'>Report Item</button>";
            }
        } catch(ErrorException $e) {
            echo $e->getMessage();
        }
    ?>
    
	<h1>Welcome to Lost And Found Engine (beta) !</h1>
	<div id="dialog-form" title="Report an item">
        <form>
        <fieldset>
            <label for="summary">Item Summary</label>
            <textarea name="summary" id="summary" rows=2 cols=55 class="text ui-widget-content ui-corner-all"></textarea>
            <p><!--space--></p>
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="text ui-widget-content ui-corner-all" />
            
            <input type="hidden" name="email" id="email" value=<?php if(isset($_SESSION['email'])) echo $_SESSION['email']; ?> />
            <input type="hidden" name="lock" id="lock" value=<?php if(isset($_SESSION['lock'])) echo $_SESSION['lock']; ?> />
        </fieldset>
        </form>
    </div>
    
    <div id="search-form">
        <input type="text" name="search_query" size=80 id="search_query" class="text ui-widget-content ui-corner-all" placeholder="Something you looking for?"/>
        <button id="search-btn">Search</button>
    </div>
    
    <div id="results-container">
	    <div id="search-result"></div>
	</div>
	
	<div id='pagination-div'>
	    <div id='pagination'></div>
	</div>
	
	<div id="recent-lost">
	    </div>
    </div>
</body>
</html>
