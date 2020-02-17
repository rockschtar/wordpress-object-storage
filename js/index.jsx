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
            items: [],
            totalPages: 0,
            totalItems: 0,
            currentPage: 1,

        };
    }

    componentDidMount() {
        this.load(this.state.skip, this.state.take);
    }

    load(skip, take) {
        superagent.get(this.restUrl + 'items').query({skip: skip, take: take})
            .set('X-WP-Nonce', this.variables.nonce).then(response => {

            this.setState({
                items: response.body.items,
                totalPages: response.body.totalPages,
                totalItems: response.body.totalItems,
                currentPage: response.body.currentPage,
            });

        })
    }


    delete(name, e) {

        let skip = this.state.skip + this.state.take;

        this.setState(prevState => ({
            items: prevState.items.filter(item => item.name !== name)
        }));

        superagent
            .delete(this.restUrl + 'delete')
            .query({name: name})
            .then(response => {
                    superagent
                        .get(this.restUrl + 'items')
                        .query({skip: skip - 1, take: 1})
                        .set('X-WP-Nonce', this.variables.nonce)
                        .then(response => {
                            this.setState({items: [...this.state.items, response.body.items[0]]});
                        });
                }
            )
    }

    handleOnFirstPage(e) {
        this.load(0, this.state.take);
    }

    handleOnLastPage(e) {
        let skip = (this.state.totalPages * this.state.take) - this.state.take;
        this.load(skip, this.state.take);
    }

    handleOnNextPage(e) {
        let skip = this.state.currentPage * this.state.take;
        this.load(skip, this.state.take);
    }

    handleOnPreviousPage(e) {
        let skip = (this.state.currentPage - 2) * this.state.take;
        this.load(skip, this.state.take);
    }

    render() {

        const {items, totalItems, totalPages, currentPage} = this.state;

        return (
            <div>
                <div class="tablenav top">
                    <Pagination
                        totalItems={totalItems}
                        totalPages={totalPages}
                        currentPage={currentPage}
                        onNextPage={(e) => this.handleOnNextPage(e)}
                        onPreviousPage={(e) => this.handleOnPreviousPage(e)}
                        onLastPage={(e) => this.handleOnLastPage(e)}
                        onFirstPage={(e) => this.handleOnFirstPage(e)}
                    />
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
                <div className="tablenav bottom">
                    <Pagination
                        totalItems={totalItems}
                        totalPages={totalPages}
                        currentPage={currentPage}
                        onNextPage={(e) => this.handleOnNextPage(e)}
                        onPreviousPage={(e) => this.handleOnPreviousPage(e)}
                        onLastPage={(e) => this.handleOnLastPage(e)}
                        onFirstPage={(e) => this.handleOnFirstPage(e)}
                    />
                </div>
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