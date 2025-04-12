class Animation
{

	find()
	{
		this.currentID = 1;
		this.items = [];
		this.amimationTypes = {"fade in": "hide", "fade out" : "show"};
		this.items = document.getElementsByClassName("animation");
		let returnItems = [];
		Array.from(this.items).forEach(element => {
			if(element.getAttribute("animationid") == this.currentID)
			{
				returnItems.push(element);
				//element.outerHTML += "<sub>" + element.getAttribute("animationid") + "</sub>";
			}
		});
		this.returnItems = returnItems;
	}

	get current()
	{
		return this.returnItems;
	}

	get next()
	{
		let returnItems = [];
		Array.from(this.items).forEach(element => {
			if(element.getAttribute("animationid") == this.currentID)
			{
				returnItems.push(element);
			}
		});
		this.currentID ++;
		this.returnItems = returnItems;
		return this.returnItems;
	}

}


class animationType
{
	
}