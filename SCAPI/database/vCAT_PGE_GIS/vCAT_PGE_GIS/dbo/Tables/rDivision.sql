CREATE TABLE [dbo].[rDivision] (
    [rDivisionID]        INT           IDENTITY (1, 1) NOT NULL,
    [DivisionUID]        VARCHAR (100) NULL,
    [ProjectID]          INT           NULL,
    [CreatedUserUID]     VARCHAR (100) NULL,
    [ModifiedUserUID]    VARCHAR (100) NULL,
    [CreatedDateTime]    DATETIME      NULL,
    [ModifiedDateTime]   DATETIME      NULL,
    [Region]             VARCHAR (50)  NULL,
    [Division]           VARCHAR (50)  NULL,
    [DCode2]             VARCHAR (50)  NULL,
    [DCode3]             VARCHAR (50)  NULL,
    [MappingOffice]      VARCHAR (50)  NULL,
    [FunctionalLocation] VARCHAR (100) NULL,
    [Revision]           INT           NULL,
    [ActiveFlag]         BIT           NULL
);

