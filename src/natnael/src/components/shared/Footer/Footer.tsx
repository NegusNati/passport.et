import { Typography } from "components/core";

export const Footer = () => {
  return (
    <footer className="flex flex-col py-[36px] px-[24px] bg-background2 border-t border-border1">
      <div className="flex flex-col max-w-[1240px] w-full mx-auto sm:hover:text-negus">
        <Typography className="text-l text-center">
          Copyright &copy; {new Date().getFullYear()} Natnael Birhanu. All Rights Reserved.
        </Typography>
      </div>
    </footer>
  );
};
