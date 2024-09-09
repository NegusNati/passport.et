import { Link } from "@inertiajs/react";

const Pagination = ({ passports, handlePageChange }) => {
    return (
        passports.data.length > 0 && (
            <div className=" font-semibold text-sm text-gray-700  flex justify-between border-t border-gray-200 pt-6   px-4 dark:border-gray-700 dark:text-black/80">
                {passports.prev_page_url && (
                    <Link
                        href={passports.prev_page_url}
                        className="pagination-link transition ease-in-out delay-100 hover:-translate-y-1 hover:scale-110 hover:text-[#FF2D20] duration-100 text-black/80"
                        onClick={(e) => {
                            handlePageChange(passports.prev_page_url);
                        }}
                    >
                        {"<"} Previous
                    </Link>
                )}
                {Array.from({ length: passports.last_page }, (_, i) => (
                    <Link
                        key={i + 1}
                        href={passports.path + `?page=${i + 1}`}
                        className={`pagination-link text-black/80 ${
                            i + 1 === passports.current_page ? "active" : ""
                        }`}
                    >
                        {i + 1}
                    </Link>
                ))}
                <p
                    id="fragment-id"
                    className="transition-all duration-500 ease-in-out"
                >
                    {passports.from + " - " + passports.to}
                </p>
                {passports.next_page_url && (
                    <Link
                        href={passports.next_page_url}
                        className="pagination-link transition ease-in-out delay-100 hover:-translate-y-1 hover:scale-110 hover:text-[#FF2D20] duration-100 text-black/80"
                        onClick={(e) => {
                            handlePageChange(passports.next_page_url);
                        }}
                    >
                        Next {">"}
                    </Link>
                )}
            </div>
        )
    );
};

export default Pagination;
