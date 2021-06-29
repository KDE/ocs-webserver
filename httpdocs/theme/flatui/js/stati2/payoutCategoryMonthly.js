!(function (d3) {

  $("#pieCategoryMonthly").empty();
  // pie payout

  var yyyymm = $( "#selectmonthCategoryMonthly option:selected" ).text();

  d3.json("/statistics/getpayoutcategorymonthly/yyyymm/"+yyyymm, function(error, data) {        

    if (error) throw error;
    data = data.data.results;
    
    // format the data
    data.forEach(function(d) {
        d.label = d.title+'['+d.amount+']';
        d.value = +d.amount;
        d.member = +d.project_category_id;
    });

    var pie = new d3pie("pieCategoryMonthly", {
        size: {
          canvasWidth: 690,
          pieOuterRadius: "90%"           
        },
        header: {
            title: {
              text: "Category payout_"+yyyymm,
              fontSize: 24,
              font: "open sans"
              }
          },
        data: {
          sortOrder: "value-desc",
          smallSegmentGrouping: {
            enabled: true,
            value: 1,
            valueType: "percentage",
            label: "<2%"
          },
          content: data
        },
      callbacks: {
          onClickSegment: function(a) {
            if(!a.data.isGrouped)
            {
                console.log(a.data);
                d3.select('#detailContainer').html('');
                var project_category_id = a.data.project_category_id;
                var title = a.data.title;
                
                if($('#pieCategoryMonthlyDetail').length==0)
                {
                    $('#pieCategoryMonthly').append('<div class="chart-wrapper" style="float:right" id="pieCategoryMonthlyDetail"> </div>');
                  } 
                var parseTime = d3.timeParse("%Y%m");                        
                d3.json("/statistics/getpayoutcategory?catid="+project_category_id, function(error, data) {                                           
                    if (error) throw error;

                    data = data.data.results;   
                    data.forEach(function (d) {                   
                       d.year = parseTime(d.yearmonth);
                       d.amount = +d.amount;                          
                    });                    
                    var chartColumns ={
                        [title]: {column: 'amount'}                       
                    };
                                    
                    var chart = makeLineChart(data, 'year',chartColumns , {xAxis: 'Month', yAxis: 'Amount'});

                    $('#pieCategoryMonthlyDetail').empty();
                    chart.bind("#pieCategoryMonthlyDetail");
                    chart.render();

                });      


                if($('#pieMemberCategoryMonthly').length==0)
                {
                    $('#pieCategoryMonthly').append('<div  style="float:right" id="pieMemberCategoryMonthly"> </div>');
                }else{
                  $('#pieMemberCategoryMonthly').empty();
                }


                d3.json("/statistics/getpayoutmemberpercategory?yyyymm="+yyyymm+'&catid='+project_category_id, function(error, data) { 

                      if (error) throw error;
                      data = data.data.results;
                      
                      // format the data
                      data.forEach(function(d) {
                          d.label = d.username+'['+d.amount+']';
                          d.value = +d.amount;
                          d.member = +d.member_id;
                      });

                      var pie = new d3pie("pieMemberCategoryMonthly", {
                          size: {
                            canvasWidth: 690,
                            pieOuterRadius: "90%"           
                          },
                          header: {
                              title: {
                                text: "member "+yyyymm,
                                fontSize: 24,
                                font: "open sans"
                                }
                            },
                          data: {
                            sortOrder: "value-desc",
                            smallSegmentGrouping: {
                              enabled: true,
                              value: 1,
                              valueType: "percentage",
                              label: "<2%"
                            },
                            content: data
                          }
                        });


                }); 



            }else{
                console.log(a);
                d3.select('#detailContainer').html('');

                var dh='<table>';
                 var da = a.data.groupedData;                   
                 dh=dh+'<tr><td>#'+da.length+'</td><td>'+a.data.value+'</td></tr>';
                 da.forEach(function(d, i) {
                      dh=dh+'<tr><td><a target="_blank" href="https://opendesktop.org/member/'+d.member+'">'+d.member+'</a></td><td>'+d.value+'</td></tr>';                      
                  });
                 dh=dh+'</table>';
                d3.select('#detailContainer').html(dh);
            }              
          }          
        }
      });

  

  });



})(d3);