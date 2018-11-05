!(function (d3) {

            var parseTime = d3.timeParse("%Y%m");
                    

                d3.json("/backend/index/getpayoutcategory?catid="+window.selectedCatid, function(error, data) {                                            
                if (error) throw error;


                var pids = data.pids;
                var pidsname = data.pidsname;   
                console.log('-----------pids---------------');
                console.log(pids);
                console.log('-----------pidsname---------------');
                console.log(pidsname);             
                data = data.results;   
                if(!data){
                     $("#payoutCategoryLineChart"+window.selectedCatid).text('no data found!');
                    return;
                }

                data.forEach(function (d) {                   
                   d.year = parseTime(d.yearmonth);
                   d.amount = +d.amount;    
                   pids.forEach(function(t){
                     d['amount'+t] = +d['amount'+t];                         
                   });
                });                    
                var chartColumns ={
                    [window.selectedCatTitle]: {column: 'amount'}                       
                };

                pidsname.forEach(function (value, i) {                        
                    var key = value;
                    chartColumns[key] ={column:'amount'+pids[i]};                        
                });                    
                var chart = makeLineChart(data, 'year',chartColumns , {xAxis: 'Month', yAxis: 'Amount'});

                $('#payoutCategoryLineChart'+window.selectedCatid).empty();
                chart.bind("#payoutCategoryLineChart"+window.selectedCatid);
                chart.render();

            });


})(d3);