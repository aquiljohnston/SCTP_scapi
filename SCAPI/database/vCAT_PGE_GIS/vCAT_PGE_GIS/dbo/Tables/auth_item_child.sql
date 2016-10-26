CREATE TABLE [dbo].[auth_item_child] (
    [parent] VARCHAR (64) NOT NULL,
    [child]  VARCHAR (64) NOT NULL,
    PRIMARY KEY CLUSTERED ([parent] ASC, [child] ASC),
    FOREIGN KEY ([child]) REFERENCES [dbo].[auth_item] ([name]),
    FOREIGN KEY ([parent]) REFERENCES [dbo].[auth_item] ([name])
);

