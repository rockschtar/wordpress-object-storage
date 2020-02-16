import React from 'react';
import ReactDOM from 'react-dom';
import superagent from 'superagent'
import Pagination from "./pagination";

class ObjectStorageBrowser extends React.Component {

    constructor(props) {
        super(props);

        this.variables = ObjectStorageBrowserVariables;
        this.restUrl = ObjectStorageBrowserVariables.resturl;

        this.state = {
            skip: 0,
            take: 20,
            items: []
        };
    }

    componentDidMount() {
        this.load(this.state.skip, this.state.take);
    }

    load(skip, take) {
        superagent.get(this.restUrl + 'items').query({skip: skip, take: take})
            .set('X-WP-Nonce', this.variables.nonce).then(response => {
            this.setState({items: response.body.items});
        })
    }


    delete(name, e) {

        let skip = this.state.skip + this.state.take;

        this.setState(prevState => ({
            items: prevState.items.filter(item => item.name !== name)
        }));

        superagent
            .delete(this.restUrl + 'get')
            .query({name: name})
            .then(response => {
                    superagent
                        .get(this.restUrl + 'items')
                        .query({skip: skip, take: 1})
                        .set('X-WP-Nonce', this.variables.nonce)
                        .then(response => {
                            this.setState({items: [...this.state.items, response.body.items[0]]});
                        });
                }
            )
    }


    render() {

        const {items} = this.state;

        return (
            <div>
                <div class="tablenav top">
                <Pagination/>
                </div>
                <table className="widefat">
                    <thead>
                    <tr>
                        <th className="row-title">Name</th>
                        <th>Value</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {items.map((item, index) => (
                        <tr class={index % 2 ? 'alternate' : ''}>
                            <td className="row-title"><label htmlFor="tablecell">{item.name}</label></td>
                            <td>{item.value}</td>
                            <td>{item.expireDateTime}</td>
                            <td><a href="#" onClick={(e) => this.delete(item.name, e)}>Delete</a></td>
                        </tr>
                    ))}
                    </tbody>
                    <tfoot>
                    <tr>
                        <th className="row-title">Name</th>
                        <th>Value</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        );
    }
}


window.onload = function () {
    ReactDOM.render(
        <ObjectStorageBrowser/>,
        document.getElementById('post-body')
    );
};