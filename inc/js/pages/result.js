var app = new Vue({
	el: "#filterlist",
	methods: {
		openCloseMenu() {
			if (window.matchMedia("(min-width: 1300px)").matches)
				document.getElementById("filterlist").open = true
			else document.getElementById("filterlist").open = false
		},
	},
	mounted: function () {
		this.openCloseMenu()
	},
})

let ffilterlist = window.matchMedia("(min-width: 1203.03px)")

ffilterlist.addEventListener("change", () => {
	e.matches
		? (document.getElementById("filterlist").open = true)
		: (document.getElementById("filterlist").open = false)
})
