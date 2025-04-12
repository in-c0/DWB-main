document.getElementById("footer").innerHTML = `
<div class="col-md-4 d-flex align-items-center">
	<a href="#" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
		<svg class="bi" width="30" height="24"><use xlink:href="#"></use></svg>
	</a>
	<span class="mb-3 mb-md-0 text-body-secondary" id="versionHolder">Copyright 2025 &copy; - BSLCS - </span>
</div>
	`;
document.getElementById("versionHolder").innerHTML += "V" + version + " All rights reserved.";

var f = document.getElementsByClassName("footer");
Array.from(document.getElementsByClassName("footer")).forEach(element => {
	element.innerHTML = `
	<div class="col-md-4 d-flex align-items-center">
		<a href="#" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
			<svg class="bi" width="30" height="24"><use xlink:href="#"></use></svg>
		</a>
		<span class="mb-3 mb-md-0 text-body-secondary" id="versionHolder">Copyright 2025 &copy; - BSLCS - </span>
	</div>
		`;
	element.innerHTML += "V" + version + " All rights reserved.";
});



