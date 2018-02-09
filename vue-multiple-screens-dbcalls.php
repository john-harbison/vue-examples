<?php 
  // Configure database connector
  define( 'DB_HOST', 'localhost' );
  define( 'DB_USER', 'root' );
  define( 'DB_PWD', '' );
  define( 'DB_NAME', 'hh-resources' );  

/////////////

  // Connect to database
  $Link = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);
  if ( !$Link ) {
    echo "Under Maintenance";
    exit();  
  }

// RETURN SINGULAR RECORD
  function getRecord($id) {
    Global $Link;
    $sql = "SELECT * FROM `hh-help_helpful` WHERE post_id = '$id' ORDER BY date DESC";
    $results = mysqli_query($Link, $sql );

    if (!$results) { echo mysqli_error($Link); echo ' <br>' .  $sql; }
    
    $rowsAll = [];
      while ($row = $results->fetch_assoc()) {
          $rowsAll[] = $row;
    }
    return $rowsAll;
  }

  //RETURN ARRAY OF RECORDS
  function getRecords() {
    Global $Link;
    $sql = "SELECT MIN(id) as id, MIN(post_url) post_url, post_id, MIN(date) date, count(post_id) as total, MIN(post_modified) post_modified FROM `hh-help_helpful` GROUP BY post_id ORDER BY id DESC";
  $results = mysqli_query($Link, $sql );

    if (!$results) { echo mysqli_error($Link); echo ' <br>' .  $sql; }
    
    $rowsAll = [];
      while ($row = $results->fetch_assoc()) {
          $rowsAll[] = $row;
    }
    return $rowsAll;
  }

   function getRecent() {
    Global $Link;
    $sql = "SELECT id, post_url, post_id, date, post_rating, post_modified, post_comment FROM `hh-help_helpful` ORDER BY id DESC";
  $results = mysqli_query($Link, $sql );

    if (!$results) { echo mysqli_error($Link); echo ' <br>' .  $sql; }
    
    $rowsAll = [];
      while ($row = $results->fetch_assoc()) {
          $rowsAll[] = $row;
    }
    return $rowsAll;
  }

  function exportToCsv($sql) {
    Global $Link;
    $date = date('Y-m-d');

    //error_log($sql);

    $results = $wpdb->get_results( $sql, $output = ARRAY_A );
    $fp = fopen('php://output', 'w');
    if ($fp && $results) {
      $file = "Export";
      $filename = $file."-".$date;

      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename=" ' . $filename . '.csv"');
      header('Pragma: no-cache');
      header('Expires: 0');
      //fputcsv($fp, $headers); 

       //while ($row = $results)) {
      foreach ($results as $row) {
       $pattern = '/<.*?>/';
       $row = preg_replace($pattern, '', array_values($row));

           fputcsv($fp, $row);
       }

       die();
  }


}


if ($_POST['get_helpful_records']) {
        if (isset($_POST['post_id'])) {
          $record = getRecord($_POST['post_id']);
          print json_encode($record);
          die();
      }

      if (isset($_POST['get_helpful_recent'])) {
        $record = getRecent();
          print json_encode($record);
          die();
      }

      if (isset($_POST['helpful_csv'])) {
        if ($_POST['helpful_sql'] == 'all') {
          $sql = 'SELECT * FROM `hh-help_helpful`';
        } else if ($_POST['helpful_sql'] == 'record') {
          $sql = 'SELECT * FROM `hh-help_helpful` WHERE post_id = ' . $_POST['helpful_post_id'];
        } else {
          die();
        }
        exportToCsv($sql);
      }
}


  $allRecords = getRecords();


  // THEN A THE END OF THE FILE
  mysqli_close($Link);
?>
<html>
	<head>
		<title> Mutliple Views Test </title>
		<style>

		</style>
    <!-- PRODUCTION vue.js framework 
		<script src="https://cdn.jsdelivr.net/npm/vue"></script>-->
        <!-- DEV VUE -->
    <script src="https://unpkg.com/vue@2.5.13/dist/vue.js"></script>
    <!-- include the jquery for ajax calls -->
    <script
  src="https://code.jquery.com/jquery-3.2.1.js"></script>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
	</head>
	<body>

 <div id="app"> 
  <!-- TEMPLATE CONTAINER -->
 <!--  <a href="#" @click="exportCsv(null)">Export To CSV</a> -->
  <div class="container">
    <component :is="currentView" :set-view="setView" :records="theRecords" :posts="thePosts" :view-record="viewRecord" :get-recent="getRecent" :the-record-title="theRecordTitle" :the-record-vals="theRecordVals" transition="fade" transition-mode="out-in"></component>
  </div>
</div>

<template id="manage-template">
  <div>
  <h3>Posts
    <div class="btn-group">
      <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="getRecent('recent')" >Recent Responses</a>
    </div>
    <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="exportCsv('Helpful_all_')" >CSV</a>
  </h3> 
    <table class="table">
      <tr>
          <th>Post ID</th>
          <th>Url </th>
          <th> Total Resp</th>
          <th> Date</th>
          <th> Post Modified</th>
      </tr>
      <tr  v-for="post in posts"> 
        <td>
          {{ post.post_id }}
        </td>
        <td>
          <a href="#"  v-on:click="viewRecord(post.post_id)"> {{ post.post_url }} </a>
        </td>
        <td>
          {{ post.total }}
        </td>
        <td>
          {{ post.date }}
        </td>
        <td>
          {{ post.post_modified }}
        </td>
      </tr>
   </table>

  </div>
</template>

<template id="manage-response">
  <div>
  <h3> Most Recent Responses: 
    <div class="btn-group">
      <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="showRecords('all')" >All</a>
      <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="showRecords('good')" >Good</a>
      <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="showRecords('bad')" >Bad</a>
       <a href="#" class="btn btn-secondary btn-sm float-right" v-on:click="setView('manage-posts')" >View By Post</a>
    </div>
  </h3> 
    <table class="table">
      <tr>
          <th>Resp ID</th>
          <th> Date</th>
          <th>Url </th>
          <th>Response</th>
          <th>Comment</th>
      </tr>
      <tr  v-for="post in records" v-bind:class="rateVal(post.post_rating)" class="records"> 
        <td>
          {{ post.id }}
        </td>
        <td>
         {{ post.date }} 
        </td>
        <td>
          <a href="#"  v-on:click="viewRecord(post.post_id)">
            {{ post.post_url }}
          </a>
        </td>
        <td>
          {{ rateVal(post.post_rating) }}
        </td>
        <td>
          {{ post.post_comment }}
        </td>
      </tr>
   </table>

  </div>
</template>

<template id="view-record">
  <div>
    <h3>ID: {{ theRecordTitle.post_id }} | <a :href="theRecordTitle.post_url" target="_blank">{{ theRecordTitle.post_url }}</a></h3>
    <a href="#" @click="setView('manage-posts')" class="btn btn-secondary btn-sm">Manage Posts</a>   
    <b>Last Comment: </b>{{theRecordVals.lastCommentDate}}  | 
    <b>Good Total:</b> {{theRecordVals.goodTotal}} | 
    <b>Bad Total:</b> {{theRecordVals.badTotal}}  | 
    <a class="btn btn-secondary btn-sm" @click="showRecords('good')" href="#">Show Good </a>
    <a class="btn btn-secondary btn-sm" @click="showRecords('bad')" href="#">Show Bad </a> 
    <a class="btn btn-secondary btn-sm" @click="showRecords('all')" href="#">All </a>
    <a class="btn btn-primary btn-sm" :href="'post.php?action=edit&post=' + theRecordTitle.post_id" target="_blank">View Post</a>
    <a class="btn btn-secondary btn-sm" v-on:click="exportCsv('Helpful_id_'+ theRecordTitle.post_id, theRecordTitle.post_id)" href="#" >CSV</a>

    
      <div v-for="record in records">
        <div class="records" v-bind:class="rateVal(record.post_rating)">
          Response ID: {{record.id}} <br>
          Response Date: {{record.date}} <br>
          Post Modified: {{record.post_modified}}<br>
          Rating: {{rateVal(record.post_rating)}} <br>
          <span v-if="record.post_comment"> Comment: {{record.post_comment}} <br></span>
          <br>
          <hr>
          <br>
        </div>
      </div>
  </div>
</template>

<script>


 Vue.component('manage-posts', {
    template: '#manage-template',
    props: ['posts', 'view-record', 'set-view', 'get-recent'],
    data: function(){
      return {
          exportCsv: function(fileName) {
            jQuery.ajax({
              method: "POST",
              url: '',
              data: {'get_helpful_records': true, 'helpful_csv': true, 'helpful_sql': 'all' },
              success: function(data) {
                var date = (new Date()).toISOString().substring(0, 10);
                var blob=new Blob([data]);
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download= fileName + '_' + date +  ".csv";
                link.click();
              }
            }); //end ajax
          } //end export
        } // end return
      } // end data
  })

 Vue.component('manage-response', {
    template: '#manage-response',
    props: ['records', 'set-view', 'view-record'],
    data: function (){
        return {
          rateVal: function(val) {
            if (val == 1) { return 'Good'; } 
            else { return 'Bad'; }
          },
          showRecords: function(showType) {
            if (showType == 'good') {
              // display good
              jQuery('.records').hide();
              jQuery('.Good').show();

            } else if (showType == 'bad') {
              // display bad
              jQuery('.records').hide();
              jQuery('.Bad').show();
            } else if (showType == 'all') {
              //display all
              jQuery('.records').show();
            }
          }, // end show records
          exportCsv: function(fileName, post_id) {
            jQuery.ajax({
              method: "POST",
              url: '',
              data: {'get_helpful_records': true, 'helpful_csv': true, 'helpful_sql': 'record', 'helpful_post_id' : post_id },
              success: function(data) {
                var date = (new Date()).toISOString().substring(0, 10);
                var blob=new Blob([data]);
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download= fileName + '_' + date +  ".csv";
                link.click();
              }
            }); //end ajax
          } //end export
        } // end  return 
    } // end data
  })


  Vue.component('view-record', {
    template: '#view-record',
    props: ['records', 'the-record-title', 'the-record-vals', 'set-view'],
    data: function (){
        return {
          rateVal: function(val) {
            if (val == 1) { return 'Good'; } 
            else { return 'Bad'; }
          },
          showRecords: function(showType) {
            if (showType == 'good') {
              // display good
              jQuery('.records').hide();
              jQuery('.Good').show();

            } else if (showType == 'bad') {
              // display bad
              jQuery('.records').hide();
              jQuery('.Bad').show();
            } else if (showType == 'all') {
              //display all
              jQuery('.records').show();
            }
          }, // end show records
          exportCsv: function(fileName, post_id) {
            jQuery.ajax({
              method: "POST",
              url: '',
              data: {'get_helpful_records': true, 'helpful_csv': true, 'helpful_sql': 'record', 'helpful_post_id' : post_id },
              success: function(data) {
                var date = (new Date()).toISOString().substring(0, 10);
                var blob=new Blob([data]);
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download= fileName + '_' + date +  ".csv";
                link.click();
              }
            }); //end ajax
          } //end export
        } // end  return 
    } // end data
  })

  var vm = new Vue({
    el: '#app',
    data: {
      currentView: 'manage-posts',
      thePosts: <?php print json_encode($allRecords); ?>,
      theRecords: '',
      theRecordTitle: {
        post_url: '',
        post_id: ''
      },
      theRecordVals: {
        lastCommentDate: '',
        goodTotal: '',
        badTotal: ''
      }
    },
    methods: {
      setView: function(viewVal){
        vm.currentView = viewVal;
      },
      viewRecord: function(pid) {
        jQuery.ajax({
              method: "POST",
              url: '',
              data: {'post_id': pid, 'get_helpful_records': true},
              dataType: "JSON"
            }).done(function(resp){
              vm.currentView = 'view-record';
              vm.theRecords = resp;
              vm.theRecordTitle.post_url = resp[0].post_url;
              vm.theRecordTitle.post_id = resp[0].post_id;
              //console.log(resp);
              resp = vm.stripslashes(resp);
              vm.theRecordVals.lastCommentDate = vm.getLastCommentDate(resp);
              var scores = vm.getRecordTotals(resp);
              vm.theRecordVals.goodTotal = scores.good;
              vm.theRecordVals.badTotal = scores.bad;
            });
      },
      getRecent: function(type) {
        jQuery.ajax({
              method: "POST",
              url: '',
              data: {'get_helpful_records': true, 'get_helpful_recent': true},
              dataType: "JSON"
            }).done(function(resp){
              vm.currentView = 'manage-response';
              vm.theRecords = resp;
              vm.theRecordTitle.post_url = resp[0].post_url;
              vm.theRecordTitle.post_id = resp[0].post_id;
              //console.log(resp);
              resp = vm.stripslashes(resp);
              vm.theRecordVals.lastCommentDate = vm.getLastCommentDate(resp);
              var scores = vm.getRecordTotals(resp);
              vm.theRecordVals.goodTotal = scores.good;
              vm.theRecordVals.badTotal = scores.bad;
            });
      },
      getLastCommentDate: function(posts) {
        var last_post_date = null;
        for (var i = posts.length - 1; i >= 0; i--) {
          if (last_post_date == null) {
            last_post_date = posts[i].date;
          } else if (posts[i].date > last_post_date) {
            last_post_date = posts[i].date;
          } else {
            continue;
          }
        }
        return last_post_date;
      },
      getRecordTotals: function(posts) {
        var scores = { 
          good: 0,
          bad: 0,
        }
        for (var i = posts.length - 1; i >= 0; i--) {
          if (posts[i].post_rating == 1) {
            scores.good++;
          } else if (posts[i].post_rating == 2) {
            scores.bad++;
          }
        }
        return scores;
      },
      stripslashes: function(posts) {
        for (var i = posts.length - 1; i >= 0; i--) {
          posts[i].post_comment = posts[i].post_comment.replace(/\\/g, '');
        }
        return posts;
      }
    }// end methods
  })
</script>

<style>
#wpfooter {
  display: none;
}
</style>







	</body>
</html>