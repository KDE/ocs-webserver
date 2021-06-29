!(function (d3) {

            var parseTime = d3.timeParse("%Y%m");
                    

                d3.json("/statistics/getproductmonthly/project_id/"+window.project_id, function(error, data) {      
                                                   
                if (error) throw error;

             
                data = data.data.results;   
                if(!data){
                     $("#topDownloadsPerMonthTabContainer_DetailMonthly").text('no data found!');
                    return;
                }

                data.forEach(function (d) {                   
                   d.year = parseTime(d.yearmonth);
                   d.amount = +d.amount;                      
                });     

                var title =project_id+'_monthly';               
                var chartColumns ={
                    [title]: {column: 'amount'}                       
                };

                            
                var chart = makeLineChart(data, 'year',chartColumns , {xAxis: 'Month', yAxis: 'Amount'});

                $('#topDownloadsPerMonthTabContainer_DetailMonthly').empty();
                chart.bind("#topDownloadsPerMonthTabContainer_DetailMonthly");
                chart.render();

            });


})(d3);