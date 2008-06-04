<html>
	<head>
		<script type="text/javascript">
			var BCMSL_R,
				BCMSL_G,
				BCMSL_B,
				BCMSL_ColorField,
				BCMSL_ColorValue;
				
			function BCMSLUpdateColorFromTextField(color)
			{
				var percval = document.getElementById(color).value;
				d
				if(percval < 0 || percval > 100)
					percval = 0;
				percval *= 2.55;
				percval = Math.round(percval);
				if(color == 'rval')
					BCMSL_R.scrollLeft = percval;
				if(color == 'gval')
					BCMSL_G.scrollLeft = percval;
				if(color == 'bval')
					BCMSL_B.scrollLeft = percval;
				BCMSLupdatecolor(color, percval);
				
			}
			function BCMSLupdatecolor(color, value)
			{
				document.getElementById(color).value = ((value/2.55).toFixed(0));
				var r,g,b;
				r = BCMSL_R.scrollLeft;
				g = BCMSL_G.scrollLeft;
				b = BCMSL_B.scrollLeft;
			
				BCMSL_ColorField.style.background = 'rgb('+r+','+g+','+b+')';
				BCMSL_ColorValue.innerHTML = BCMSLdechex(r)+BCMSLdechex(g)+BCMSLdechex(b)+'';
			}
			function BCMSLdechex(dec)
			{
				var hx = new Array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
				var be,le;
				be = Math.round(dec/16);
				if(be > (dec/16))be--;
				le = ((dec/16) - be)*16;
				return hx[be]+hx[le]+'';
			}
			function BCMSLinsertColor()
			{
				
			}
			function BCMSLinit()
			{
				BCMSL_R = document.getElementById('rscroll');
				BCMSL_G = document.getElementById('gscroll');
				BCMSL_B = document.getElementById('bscroll');
				
				BCMSL_ColorField = document.getElementById('color');
				BCMSL_ColorValue = document.getElementById('hxcolor');
				
				BCMSL_R.scrollLeft = 0;
				BCMSL_G.scrollLeft = 0;
				BCMSL_B.scrollLeft = 0;
			}
		</script>
		<link rel="stylesheet" href="../ClientData/Stylesheets/Leaf.css" />
	</head>
	<body>
		<div id="colorselector" class="BambusLeafFrame">
			<div class="BambusLeafColorBox">
				<a href="javascript:insertColor();" id="color"></a>
				#<span id="hxcolor">000000</span>
			</div>
			<div id="rscroll" class="BambusLeafOuterScroll" onmousemove="BCMSLupdatecolor('rval', this.scrollLeft);" onmousedown="BCMSLupdatecolor('rval', this.scrollLeft);" onmouseup="BCMSLupdatecolor('rval', this.scrollLeft);">
				<div class="BambusLeafInnerScroll"></div>
			</div>
			R: 
			<input id="rval" onkeyup="BCMSLUpdateColorFromTextField('rval')" type="text" value="0" size="3" style="border:1px solid black;text-align:right;" />%
			
			<div id="gscroll" class="BambusLeafOuterScroll" onmousemove="BCMSLupdatecolor('gval', this.scrollLeft);" onmousedown="BCMSLupdatecolor('gval', this.scrollLeft);" onmouseup="BCMSLupdatecolor('gval', this.scrollLeft);">
				<div class="BambusLeafInnerScroll"></div>
			</div>
			G: 
			<input id="gval" onkeyup="BCMSLUpdateColorFromTextField('gval')" type="text" value="0" size="3" style="border:1px solid black;text-align:right;" />%
			
			<div id="bscroll" class="BambusLeafOuterScroll" onmousemove="BCMSLupdatecolor('bval', this.scrollLeft);" onmousedown="BCMSLupdatecolor('bval', this.scrollLeft);" onmouseup="BCMSLupdatecolor('bval', this.scrollLeft);">
				<div class="BambusLeafInnerScroll"></div>
			</div>
			B: 
			<input id="bval" onkeyup="BCMSLUpdateColorFromTextField('bval')" type="text" value="0" size="3" style="border:1px solid black;text-align:right;" />%
			<script type="text/javascript">
				BCMSLinit();
			</script>
		</div>
	</body>
</html>
