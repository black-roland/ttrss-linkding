Plugins.Linkding = {

	shareArticleToLinkding: function(id, btn) {
		try {

			var d = new Date();
		    var ts = d.getTime();

			Notify.progress("Saving to Linkding…", true);
			xhrPost("backend.php",
			{
				op: "pluginhandler",
				plugin: "Linkding",
				method: "getInfo",
				id: encodeURIComponent(id)
			},
			(transport) => {
				var ti = JSON.parse(transport.responseText);
				if (ti.status=="200" || ti.status=="201") {
					Notify.info("Saved to Linkding:<br/><em>" + ti.title + "</em>");
					btn.outerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"><path fill="#5856E0" fill-rule="evenodd" d="M14.992 7.965a7.024 7.024 0 1 1-14.047 0 7.023 7.023 0 0 1 14.047 0Zm0 0"/><path fill="none" stroke="#FFF" stroke-linejoin="round" stroke-miterlimit="1.5" stroke-width=".9765625" d="M7.785 3.457 3.371 7.871s-1.516 1.48.133 3.176c1.66 1.703 3.18.133 3.18.133l4.41-4.41"/><path fill="none" stroke="#FFF" stroke-linejoin="round" stroke-miterlimit="1.5" stroke-width=".9765625" d="m8.246 12.516 4.398-4.43s1.512-1.488-.144-3.176c-1.664-1.695-3.18-.12-3.18-.12L4.926 9.214"/></svg>';
					btn.title='Saved to Linkding';
				}
				else {
					Notify.error("<strong>Error saving to Linkding!</strong><br/>("+ti.status+") "+ti.message);
				}
			}
			);

		} catch (e) {
			App.Error.report(e);
		}
	}
};

require(['dojo/_base/kernel', 'dojo/ready'], function (dojo, ready) {
	ready(function () {
		PluginHost.register(PluginHost.HOOK_INIT_COMPLETE, () => {
			App.hotkey_actions['linkding_save'] = function() {
				if (Article.getActive()) {
					var artid = "ld"+Article.getActive();
					Plugins.Linkding.shareArticleToLinkding(Article.getActive(), document.getElementById(artid));
					return;
				}
			};
		});
	});
});
