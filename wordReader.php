<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Reader</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script><script src="js/nav.js"></script>
	<script src="js/utils.js"></script>
</head>

<body>
	<span> IN ALPHA SO MAY NOT HAVE ALL FEATURES </span><br>
	<span> Load word documents and create a question from the content </span> <br>
<input type="file" id="upload" accept=".docx" /><br>
<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="createQuestion()">CREATE QUESTION</button>
<div class="container23">
    <div class="container__right2" id ="output" contenteditable="true">R
	</div>
</div>
	<style>
		table, th, td {
  			border: 1px solid black;
		}

		.container2 {
			display: flex;

			/* Misc */
			border: 1px solid #cbd5e0;
			height: 35rem;
			width: 100%;
		}

		.container__left {
			/* Initially, the left takes 3/4 width */
			width: 75%;
			text-wrap: wrap;
			/* Misc */
		}
		.resizer {
			background-color: #cbd5e0;
			cursor: ew-resize;
			height: 100%;
			width: 2px;
		}
		.container__right {
			/* Take the remaining width */
			text-wrap: wrap;
			flex: 1;

		}
	</style>
    <script>
		var id = 0;
        document.getElementById('upload').addEventListener('change', function (event) {
            let reader = new FileReader();
            reader.onload = function (event) {
                mammoth.convertToHtml({ arrayBuffer: event.target.result })
                    .then(function (result) {
						console.log(result.value);
						var cleanResult = result.value;
						cleanResult.replaceAll("<table", "<table border='2'");
                        document.getElementById('output').innerHTML = cleanResult;
						cleanDoc();
                    })
                    .catch(function (err) {
                        console.log(err);
                    });
            };
            reader.readAsArrayBuffer(event.target.files[0]);
        });

		function cleanDoc()
		{			
            var itemsToRemove = ["<h2>","</h2>","<strong>"];
			var lines = [];
			document.getElementById('output').innerHTML.split("</table>").forEach(element => {
				element = element.replaceAll("<table", `<table border="2" id="` + id + `"`).replaceAll("<td></td>", `<td>&nbsp;</td>`);
                itemsToRemove.forEach(element2 => {
                    element = element.replaceAll(element2,"");
                });
				id++;
				lines.push(element + '</table>');
			});
			console.log(lines);
			document.getElementById('output').innerHTML = lines.join("");
		}

		function createQuestion()
		{
			
		}
        
		document.addEventListener('DOMContentLoaded', function () {
    // Query the element
    const resizer = document.getElementById('dragMe');
    const leftSide = resizer.previousElementSibling;
    const rightSide = resizer.nextElementSibling;

    // The current position of mouse
    let x = 0;
    let y = 0;
    let leftWidth = 0;

    // Handle the mousedown event
    // that's triggered when user drags the resizer
    const mouseDownHandler = function (e) {
        // Get the current mouse position
        x = e.clientX;
        y = e.clientY;
        leftWidth = leftSide.getBoundingClientRect().width;

        // Attach the listeners to document
        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
    };

    const mouseMoveHandler = function (e) {
        // How far the mouse has been moved
        const dx = e.clientX - x;
        const dy = e.clientY - y;

        const newLeftWidth = ((leftWidth + dx) * 100) / resizer.parentNode.getBoundingClientRect().width;
        leftSide.style.width = newLeftWidth + '%';

        resizer.style.cursor = 'col-resize';
        document.body.style.cursor = 'col-resize';

        leftSide.style.userSelect = 'none';
        leftSide.style.pointerEvents = 'none';

        rightSide.style.userSelect = 'none';
        rightSide.style.pointerEvents = 'none';
    };

    const mouseUpHandler = function () {
        resizer.style.removeProperty('cursor');
        document.body.style.removeProperty('cursor');

        leftSide.style.removeProperty('user-select');
        leftSide.style.removeProperty('pointer-events');

        rightSide.style.removeProperty('user-select');
        rightSide.style.removeProperty('pointer-events');

        // Remove the handlers of mousemove and mouseup
        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
    };

    // Attach the handler
    resizer.addEventListener('mousedown', mouseDownHandler);
});
    </script>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
	<script src="js/footer.js"></script>
</footer>
</html>