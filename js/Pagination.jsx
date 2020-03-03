import React from "react";
import {__, _n, sprintf} from "@wordpress/i18n"

export default class Pagination extends React.Component {

    constructor(props) {
        super(props);


        this.state = {
            totalPages: this.props.totalPages,
            currentPage: this.props.currentPage,
            totalItems: this.props.totalItems
        };
    }

    componentDidUpdate() {

    }

    handleChange(e) {


        if (this.props.onChange) {
            this.props.onChange(e);
        }
    }

    handleOnFirstPage(e) {
        if (this.props.onFirstPage) {
            this.props.onFirstPage(e);
        }

    }

    handleOnLastPage(e) {
        if (this.props.onLastPage) {
            this.props.onLastPage(e);
        }
    }

    handleOnNextPage(e) {
        if (this.props.onNextPage) {
            this.props.onNextPage(e);
        }
    }

    handleOnPreviousPage(e) {
        if (this.props.onPreviousPage) {
            this.props.onPreviousPage(e);
        }
    }

    render() {

        const {totalItems, totalPages, currentPage} = this.props;

        let currentPageIsGreaterOne = currentPage > 1;
        let currentPageIsOne = currentPage === 1;
        let currentPageIsLast = currentPage === totalPages;
        let currentPageIsSmallerLast = currentPage < totalPages;


        return (
            <div className="tablenav-pages">
                <span className="displaying-num">{sprintf(_n('One item', '%s items', totalItems, 'rs-object-storage'), totalItems)}</span>
                <span className="pagination-links">
                     {currentPageIsGreaterOne ? (
                         <a className="first-page button" href="#" onClick={(e) => this.handleOnFirstPage(e)}><span
                             className="screen-reader-text">{ __('First page', 'rs-object-storage')}</span><span aria-hidden="true">«</span></a>
                     ) : (
                         <span className="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                     )}

                    {currentPageIsOne ? (
                        <span className="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                    ) : (
                        <a className="prev-page button" href="#" onClick={(e) => this.handleOnPreviousPage(e)}><span
                            className="screen-reader-text">{__('Previous page', 'rs-object-storage')}</span><span aria-hidden="true">‹</span></a>
                    )}

                    <span className="paging-input">
                        <label htmlFor="current-page-selector" className="screen-reader-text">{__('Current Page', 'rs-object-storage')}</label>
                        <input className="current-page" id="current-page-selector" type="text" name="paged"
                               value={currentPage} size="3" aria-describedby="table-paging"/>

                        <span className="tablenav-paging-text"> von <span className="total-pages">{totalPages}</span></span>

                    </span>
                    {currentPageIsSmallerLast ? (
                        <a className="next-page button"
                           href="#" onClick={(e) => this.handleOnNextPage(e)}>
                            <span className="screen-reader-text">Nächste Seite</span>
                            <span aria-hidden="true">›</span>
                        </a>
                    ) : (
                        <span className="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                    )}
                    {currentPageIsLast ? (
                        <span className="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                    ) : (
                        <a className="last-page button" href="#" onClick={(e) => this.handleOnLastPage(e)}>
                            <span className="screen-reader-text">Letzte Seite</span>
                            <span aria-hidden="true">»</span>
                        </a>
                    )}




                        </span>
            </div>
        )
    }

}