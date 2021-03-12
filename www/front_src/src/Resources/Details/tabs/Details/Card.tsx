import * as React from 'react';

import { Paper, makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  container: {
    height: '100%',
  },
  content: {
    padding: theme.spacing(1, 2, 2, 2),
  },
  title: {
    display: 'flex',
    gridGap: theme.spacing(1),
  },
  active: {
    color: theme.palette.success.main,
  },
}));

interface Props {
  children?: React.ReactNode;
  className?: string;
}

const Card = ({ children, className }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Paper className={className}>
      <div className={classes.content}>{children}</div>
    </Paper>
  );
};

export default Card;
