import React from "react";

export default class Pagination extends React.Component {


    constructor(props) {
        super(props);

        this.state = {
            totalPages: 0,
            currentPage: 1
        };
    }

    render() {


        return (
            <div className="tablenav-pages">
                <span className="displaying-num">3.150 Einträge</span>
                <span className="pagination-links">
                    <span className="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                    <span className="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                    <span className="paging-input">
                        <label htmlFor="current-page-selector" className="screen-reader-text">Aktuelle Seite</label>
                        <input className="current-page" id="current-page-selector" type="text" name="paged" value="1" size="3" aria-describedby="table-paging" />
                        <span className="tablenav-paging-text"> von <span className="total-pages">158</span></span>
                    </span>
                    <a className="next-page button" href="https://www.clubfans-united.de/wp/wp-admin/edit.php?paged=2">
                        <span className="screen-reader-text">Nächste Seite</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a className="last-page button" href="https://www.clubfans-united.de/wp/wp-admin/edit.php?paged=158">
                        <span className="screen-reader-text">Letzte Seite</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </span>
            </div>
        )
    }

}