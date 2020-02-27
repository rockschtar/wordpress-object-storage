import React from 'react';
import ReactDOM from 'react-dom';
import superagent from 'superagent'
import Pagination from "./Pagination";
import ReactModal from "react-modal";
import {__, sprintf} from "@wordpress/i18n"
import "flatpickr/dist/themes/light.css";
import * as moment from "moment/moment"
import {German} from "flatpickr/dist/l10n/de.js"
import {Spanish} from "flatpickr/dist/l10n/es.js"
import "./ObjectStorageBrowser.css"


class ObjectStorageBrowser extends React.Component {

    constructor(props) {

        super(props);

        // noinspection JSUnresolvedVariable
        this.variables = ObjectStorageBrowserVariables;

        this.flatPickrOptions = {};
        this.flatPickrOptions.mindate = moment().add(30, 'm').toDate();

        switch (this.variables.locale) {
            case 'de':
                this.flatPickrOptions.locale = German;
                break;
            case 'es':
                this.flatPickrOptions.locale = Spanish;
                break;
        }

        this.state = {
            isLoading: true,
            currentSkip: 0,
            take: 20,
            items: [],
            totalPages: 0,
            totalItems: 0,
            currentPage: 1,
            orderBy: 'name',
            orderName: 'asc',
            orderExpireTimestamp: 'asc'
        };
    }

    componentDidMount() {
        this.load(0, this.state.take);
    }

    async orderExpireTimestamp(order) {
        await this.setState({orderBy: 'expireTimestamp', orderExpireTimestamp: order});
        this.load(this.state.currentSkip, this.state.take);
    }

    async orderName(order) {
        await this.setState({orderBy: 'name', orderName: order});
        this.load(this.state.currentSkip, this.state.take);
    }

    load(skip, take) {

        this.setState({isLoading: true});

        let order = 'asc';

        switch (this.state.orderBy) {
            case 'name':
                order = this.state.orderName;
                break;
            case 'expireTimestamp':
                order = this.state.orderExpireTimestamp;
                break;
        }

        superagent.get(this.variables.resturl + 'items').query(
            {
                skip: skip,
                take: take,
                orderBy: this.state.orderBy,
                order: order
            }
        ).set('X-WP-Nonce', this.variables.nonce).then(response => {

            this.setState({
                isLoading: false,
                currentSkip: skip,
                items: response.body.items,
                totalPages: response.body.totalPages,
                totalItems: response.body.totalItems,
                currentPage: response.body.currentPage,
            });

        })
    }

    delete(name, e) {

        let skip = this.state.currentSkip + this.state.take;

        this.setState({isLoading: true});

        superagent
            .delete(this.variables.resturl + 'delete')
            .query({name: name})
            .then(response => {

                    this.setState(prevState => ({
                        items: prevState.items.filter(item => item.name !== name)
                    }));

                    if (this.state.items.length === 0 && this.state.currentPage > 1) {
                        this.loadPreviousPage()
                        this.setState({isLoading: false});
                    } else {
                        superagent
                            .get(this.variables.resturl + 'items')
                            .query({skip: skip - 1, take: 1})
                            .set('X-WP-Nonce', this.variables.nonce)
                            .then(response => {
                                if (response.body.items.length > 0) {
                                    this.setState({
                                        isLoading: false,
                                        totalItems: response.body.totalItems,
                                        items: [...this.state.items, response.body.items[0]]
                                    });
                                }
                            });
                    }
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
        this.loadPreviousPage();
    }

    loadPreviousPage() {
        let skip = (this.state.currentPage - 2) * this.state.take;
        this.load(skip, this.state.take);
    }

    render() {

        const {items, totalItems, totalPages, currentPage, isLoading} = this.state;

        return (
            <div>
                <div className="tablenav top">
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
                <table className={"widefat " + (this.state.isLoading ? 'loading' : '')}>
                    <thead>
                    <tr>
                        <th className={"manage-column column-title column-primary sortable " + this.state.orderName}>
                            <a href="#" onClick={() => this.orderName(this.state.orderName === 'asc' ? 'desc' : 'asc')}>
                                <span>{__('Name', 'rs-object-storage')}</span>
                                <span className="sorting-indicator"/>
                            </a>
                        </th>
                        <th>{__('Value', 'rs-object-storage')}</th>
                        <th className={"manage-column column-title column-primary sortable " + this.state.orderExpireTimestamp}>
                            <a href="#"
                               onClick={() => this.orderExpireTimestamp(this.state.orderExpireTimestamp === 'asc' ? 'desc' : 'asc')}>
                                <span>{__('Expires', 'rs-object-storage')}</span>
                                <span className="sorting-indicator"/></a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {items.length === 0 && !isLoading && (
                        <tr colspan="4" align="center">{__('No Items found.')}</tr>
                    )}
                    {items.map((item, index) => (
                        <tr className={index % 2 ? 'alternate' : ''}>
                            <td className="row-title"><label htmlFor="tablecell">{item.name}</label>

                                <div className="row-actions">
                                    <span className="edit"><a href="#"
                                                              onClick={(e) => this.editModal().handleOpen(index, item)}
                                                              aria-label={sprintf(__('Edit %s', 'rs-objeect-storage'), item.name)}>{__('Edit', 'rs-object-storage')}</a> | </span>
                                    <span className="trash"><a href="#" onClick={(e) => this.delete(item.name, e)}
                                                               className="submitdelete"
                                                               aria-label="Move “Hello world!” to the Trash">Delete</a> |</span>
                                    <span className="view"><a href="#" rel="bookmark"
                                                              aria-label="View “{item.name}”">View</a></span>
                                </div>

                            </td>
                            <td>{item.value}</td>
                            <td>{item.expireDateTime}</td>
                        </tr>
                    ))}
                    </tbody>
                    <tfoot>
                    <tr>
                        <th className={"manage-column column-title column-primary sortable " + this.state.orderName}>
                            <a href="#" onClick={() => this.orderName(this.state.orderName === 'asc' ? 'desc' : 'asc')}>
                                <span>{__('Name', 'rs-object-storage')}</span>
                                <span className="sorting-indicator"/>
                            </a>
                        </th>
                        <th>{__('Value', 'rs-object-storage')}</th>
                        <th className={"manage-column column-title column-primary sortable " + this.state.orderExpireTimestamp}>
                            <a href="#"
                               onClick={() => this.orderExpireTimestamp(this.state.orderExpireTimestamp === 'asc' ? 'desc' : 'asc')}>
                                <span>{__('Expires', 'rs-object-storage')}</span>
                                <span className="sorting-indicator"/></a>
                        </th>
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

    ReactModal.setAppElement('#post-body');

    ReactDOM.render(
        <ObjectStorageBrowser/>,
        document.getElementById('post-body')
    );
};