import React from 'react';
import ReactDOM from 'react-dom';
import superagent from 'superagent'
import Pagination from "./pagination";
import ReactModal from "react-modal";
import {__, sprintf} from "@wordpress/i18n"
import "flatpickr/dist/themes/light.css";
import Flatpickr from "react-flatpickr";
import * as moment from "moment/moment"
import {German} from "flatpickr/dist/l10n/de.js"
import {Spanish} from "flatpickr/dist/l10n/es.js"

class ObjectStorageBrowser extends React.Component {

    constructor(props) {

        super(props);

        this.variables = ObjectStorageBrowserVariables;
        this.restUrl = ObjectStorageBrowserVariables.resturl;


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


        this.order = (orderBy, order) => {
            return {
                orderBy: orderBy,
                orderDirection: order
            }
        };

        this.state = {
            isLoading: true,
            currentSkip: 0,
            take: 20,
            items: [],
            totalPages: 0,
            totalItems: 0,
            currentPage: 1,
            editShow: false,
            editItem: null,
            orderTest : new this.order('name', 'asc'),
            orderBy: 'name',
            orderName: 'asc',
            orderExpireTimestamp: 'asc'
        };
    }

    componentDidMount() {
        this.load(0, this.state.take);
    }


    editModal() {

        return {

            handleOpen: (index, item) => {

                let editItem = {};
                editItem.original = item;
                editItem.index = index;
                editItem.name = item.name;
                editItem.date = new Date(item.expireTimestamp * 1000);

                this.setState({
                    editItem: editItem,
                    editShow: true
                });

            },
            handleClose: () => {
                this.setState({
                    editItem: null,
                    editShow: true
                });
            },
            onChangeName: (event) => {

                console.log(event);

                let editItem = this.state.editItem;
                editItem.name = event.target.value;

                this.setState({
                    editItem: editItem
                });
            },
            onChangeExpires: (date) => {

                let realDate = new Date(date);


                let editItem = this.state.editItem;
                editItem.date = realDate;
                editItem.expireTimestamp = realDate.getTime() / 1000;

                this.setState({
                    editItem: editItem
                });

                console.log(realDate.toDateString());

            },
            getMinDate: () => {
                return moment().add(30, 'm').toDate();
            },
            update: (editItem) => {

                let params = {};

                params.newName = editItem.name
                params.name = editItem.original.name;
                params.expireTimestamp = editItem.expireTimestamp;

                superagent
                    .post(this.restUrl + 'update')
                    .send(params)
                    .set('X-WP-Nonce', this.variables.nonce)
                    .then(response => {

                        //replace item

                        console.log(response);
                    });
            }
        }
    };

    async order(order) {




        await this.setState({orderTest: order});

        this.load(this.state.currentSkip, this.state.take);
    }

    getOrderDirection() {

        switch(this.state.order) {
            case 'name':
                return this.state.orderName;
            case 'expireTimestamp':
                return this.state.orderExpireTimestamp;
        }

        return null;

    }

    load(skip, take) {

        this.setState({isLoading: true});

        superagent.get(this.restUrl + 'items').query(
            {
                skip: skip,
                take: take,
                orderBy: this.state.orderTest.orderBy,
                order: this.state.orderTest.order
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
            .delete(this.restUrl + 'delete')
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
                            .get(this.restUrl + 'items')
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

    onChangeEdit(event) {

    }

    render() {

        const {items, totalItems, totalPages, currentPage, isLoading, editShow, editItem} = this.state;


        let minDate = this.editModal().getMinDate();

        console.log(minDate);


        const customStyles = {
            content: {
                top: '20%',
                left: '50%',
                right: 'auto',
                bottom: 'auto',
                marginRight: '-50%',
                transform: 'translate(-50%, -50%)'
            }
        };

        return (
            <div>

                <ReactModal
                    isOpen={editShow}
                    style={customStyles}
                    onRequestClose={() => this.editModal().handleClose()}
                    contentLabel="Minimal Modal Example">
                    <table className="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label htmlFor="name">Name</label>
                            </th>
                            <td>
                                <input name="name" type="text" className="regular-text"
                                       defaultValue=""
                                       onChange={(e) => this.editModal().onChangeName(e)}
                                       value={editItem && editItem.name}/>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label htmlFor="expireDate">Expires</label>
                            </th>
                            <td>
                                <Flatpickr
                                    data-enable-time
                                    options={this.flatPickrOptions}
                                    value={editItem && editItem.date}
                                    onChange={date => {
                                        this.editModal().onChangeExpires(date);
                                    }}
                                />
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <p className={"submit"}>
                        <button className={"button-secondary"} onClick={() => this.editModal().handleClose()}>Close
                        </button>
                        &nbsp;&nbsp;&nbsp;
                        <button className={"button-primary"} onClick={(e) => this.editModal().update(editItem)}>Update
                        </button>
                    </p>
                </ReactModal>

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
                <table className="widefat">
                    <thead>
                    <tr>
                        <th className={"manage-column column-title column-primary sortable " + this.state.order}>
                            <a href="#"
                               onClick={() => this.order(new this.order('name', 'asc'))}><span>{__('Name', 'rs-object-storage')}</span><span
                                className="sorting-indicator"></span></a>


                        </th>
                        <th>{__('Value', 'rs-object-storage')}</th>
                        <th className={"manage-column column-title column-primary sortable " + this.state.order}>

                            <a href="#"
                               onClick={() => this.order('expireTimestamp', this.state.order === 'asc' ? 'desc' : 'asc')}><span>{__('Expires', 'rs-object-storage')}</span><span
                                className="sorting-indicator"></span></a>
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
                        <th className="row-title">Name</th>
                        <th>Value</th>
                        <th>Expires</th>
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