!(function (d3) {


  var margin = {top: 20, right: 20, bottom: 80, left: 40},
      width = 1240 - margin.left - margin.right,
      height = 500 - margin.top - margin.bottom;
            
  // append the svg object to the body of the page
  // append a 'group' element to 'svg'
  // moves the 'group' element to the top left margin
  var svg = d3.select("#newproductsweekly").append("svg")
      .attr("width", width + margin.left + margin.right)
      .attr("height", height + margin.top + margin.bottom);
    g = svg.append("g")
      .attr("transform", 
            "translate(" + margin.left + "," + margin.top + ")");

  // set x scale
  var x = d3.scaleBand()
      .rangeRound([0, width])
      .paddingInner(0.05)
      .align(0.1);

  // set y scale
  var y = d3.scaleLinear()
      .rangeRound([height, 0]);

  // set the colors
  var z = d3.scaleOrdinal()
      .range(["#8a89a6", "#98abc5", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00"]);

  // load the csv and create the chart
  d3.json("/statistics/new-products-weekly-json", function(error, data) {        
    if (error) throw error;
    data = data.data.results;

    data.columns=['yyyykw','amountnowallpapers','amountwallpapers']; 
    // format the data
    data.forEach(function(d) {
        d.yyyykw = d.yyyykw;
        d.amountwallpapers = +d.amountwallpapers;
        d.amountnowallpapers = +d.amountnowallpapers;
        d.total = d.amountwallpapers+d.amountnowallpapers;
    });



    var keys = data.columns.slice(1);
  
    x.domain(data.map(function(d) { return d.yyyykw; }));
    y.domain([0, d3.max(data, function(d) { return d.total; })]).nice();
    z.domain(keys);

    g.append("g")
      .selectAll("g")
      .data(d3.stack().keys(keys)(data))
      .enter().append("g")
        .attr("fill", function(d) { return z(d.key); })
      .selectAll("rect")
      .data(function(d) { return d; })
      .enter().append("rect")
        .attr("x", function(d) { return x(d.data.yyyykw); })
        .attr("y", function(d) { return y(d[1]); })
        .attr("height", function(d) { return y(d[0]) - y(d[1]); })
        .attr("width", x.bandwidth())
      .on("mouseover", function() { tooltip.style("display", null); })
      .on("mouseout", function() { tooltip.style("display", "none"); })
      .on("mousemove", function(d) {
        console.log(d);
        var xPosition = d3.mouse(this)[0] - 5;
        var yPosition = d3.mouse(this)[1] - 5;
        tooltip.attr("transform", "translate(" + xPosition + "," + yPosition + ")");
        tooltip.select("text").text(d.data.yyyykw+':'+(d[1]-d[0]));
      });

    g.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x))
        .selectAll("text")
        .attr("y", 0)
        .attr("x", 9)
        .attr("dy", ".35em")
        .attr("transform", "rotate(90)")
        .style("text-anchor", "start");
        

    g.append("g")
        .attr("class", "axis")
        .call(d3.axisLeft(y).ticks(null, "s"))
      .append("text")
        .attr("x", 2)
        .attr("y", y(y.ticks().pop()) + 0.5)
        .attr("dy", "0.32em")
        .attr("fill", "#000")
        .attr("font-weight", "bold")
        .attr("text-anchor", "start");

    var legend = g.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", 10)
        .attr("text-anchor", "end")
      .selectAll("g")
      .data(keys.slice().reverse())
      .enter().append("g")
        .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });

    legend.append("rect")
        .attr("x", width - 19)
        .attr("width", 19)
        .attr("height", 19)
        .attr("fill", z);


    legend.append("text")
        .attr("x", width - 24)
        .attr("y", 9.5)
        .attr("dy", "0.32em")
        .text(function(d) { 

          if(d=='amountnowallpapers') return 'Non-Wallpapers';
          if(d=='amountwallpapers') return 'Wallpapers';
          // return d; 
        });
  });

    // Prep the tooltip bits, initial display is hidden
    var tooltip = svg.append("g")
      .attr("class", "d3-tip")
      .style("display", "none");
        
    tooltip.append("rect")
      .attr("width", 60)
      .attr("height", 20)
      .attr("fill", "white")
      .style("opacity", 0.5);

    tooltip.append("text")
      .attr("x", 30)
      .attr("dy", "1.2em")
      .style("text-anchor", "middle")
      .attr("font-size", "12px")
      .attr("font-weight", "bold");



})(d3);