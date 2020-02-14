import React from 'react';
import ReactDOM from 'react-dom';
import superagent from 'superagent';


class ObjectStorageBrowser extends React.Component {

    constructor(props) {
        super(props);
        this.getList();
    }

    getList() {

        superagent.get(ObjectStorageBrowserVariables.resturl + 'get').set('X-WP-Nonce', ObjectStorageBrowserVariables.nonce).then(res => {

            console.log(res);

        })

    }


    render() {
        return (
            <table className="widefat">
                <thead>
                <tr>
                    <th className="row-title">Header</th>
                    <th>Titel</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td className="row-title"><label htmlFor="tablecell">Cell 1</label></td>
                    <td>fewrere</td>
                </tr>
                <tr className="alternate">
                    <td className="row-title"><label htmlFor="tablecell">Woeoee <code>alternate</code></label></td>
                    <td>dsfsdfdsfsdf</td>
                </tr>
                <tr>
                    <td className="row-title">sdfdfsfsf</td>
                    <td>dsfdsfdsfsdfs</td>
                </tr>
                <tr className="alt">
                    <td className="row-title">dsfdsfsdfsdfdsf<code>alt</code></td>
                    <td>sdfsdfsdfsdfsfsdfs</td>
                </tr>
                <tr className="form-invalid">
                    <td className="row-title">dfsdfdsfdsfds<code>form-invalid</code></td>
                    <td>sdfsdfdsfsdfsdfsdf</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th className="row-title">dsfdsfdsfdsfds</th>
                    <th>dfdsfdsfdsf</th>
                </tr>
                </tfoot>
            </table>
        );
    }

}


window.onload = function() {
    ReactDOM.render(
        <ObjectStorageBrowser/>,
        document.getElementById('post-body')
    );
};