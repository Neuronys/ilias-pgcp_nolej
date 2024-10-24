var pcnlj_modal = null;

/**
 * Setup the activity selector modal.
 * @param {text} id of the modal
 * @param {text} signal id of the replace signal
 * @param {text} url to be called by modal
 */
function pcnlj_setup_modal(id, signal, url) {
    if (pcnlj_modal == null) {
        pcnlj_modal = {
            id: id,
            signal: signal,
            url: url
        };
    }
}

/**
 * Show the activity selector modal.
 * @param {number} ref_id of Nolej object
 */
function pcnlj_show_modal(ref_id) {
    if (pcnlj_modal == null) {
        console.log("Modal not found.");
        return;
    }

    if (!pcnlj_modal.hasOwnProperty("signal")) {
        console.log("Signal missing.");
        return;
    }

    let url = pcnlj_modal.url + "&sel_ref_id=" + ref_id;
    il.UI.modal.replaceFromSignal(pcnlj_modal.id, {options: {url: url}});
    il.UI.modal.showModal(pcnlj_modal.id, {}, {});
}
